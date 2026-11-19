<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ParentSummon;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Services\CsvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminKedisiplinanController extends Controller
{
    // ==========================================
    // 0. PENCARIAN SISWA (AJAX / JSON)
    // ==========================================

    /**
     * Pencarian siswa secara dinamis untuk komponen live search.
     * Mengembalikan JSON berisi daftar siswa yang cocok dengan query.
     */
    public function searchStudents(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen(trim($query)) < 1) {
            return response()->json([]);
        }

        $students = Student::with('schoolClass')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('nis', 'like', "%{$query}%")
                  ->orWhere('nisn', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function ($student) {
                return [
                    'id'         => $student->id,
                    'name'       => $student->name,
                    'nis'        => $student->nis,
                    'nisn'       => $student->nisn,
                    'class_name' => $student->schoolClass?->name ?? '-',
                    'gender'     => $student->gender,
                    'total_points' => $student->total_violation_points,
                ];
            });

        return response()->json($students);
    }

    // ==========================================
    // 1. JENIS PELANGGARAN
    // ==========================================

    public function violationTypesIndex(): View
    {
        $types = ViolationType::withCount('violations')->orderBy('category')->orderBy('points')->get();

        return view('kedisiplinan.types.index', compact('types'));
    }

    public function violationTypeStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:violation_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:ringan,sedang,berat'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'default_action' => ['nullable', 'string'],
        ]);

        ViolationType::create($validated);
        AuditLog::record('create_violation_type', "Menambah jenis pelanggaran {$validated['name']} ({$validated['code']})");

        return back()->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    // ==========================================
    // 2. CATATAN PELANGGARAN SISWA
    // ==========================================

    public function violationsIndex(Request $request): View
    {
        $query = Violation::with(['student.schoolClass', 'violationType', 'recordedByUser']);

        if ($request->filled('category')) {
            $query->whereHas('violationType', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $violations = $query->orderBy('incident_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        $violationTypes = ViolationType::orderBy('category')->orderBy('name')->get();
        $students = Student::with('schoolClass')->orderBy('name')->get();

        return view('kedisiplinan.violations.index', compact('violations', 'classes', 'violationTypes', 'students'));
    }

    public function violationStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'violation_type_id' => ['required', 'exists:violation_types,id'],
            'incident_date' => ['required', 'date'],
            'incident_time' => ['nullable'],
            'location' => ['nullable', 'string', 'max:255'],
            'chronology' => ['required', 'string'],
            'follow_up_action' => ['nullable', 'string'],
            'status' => ['required', 'in:tercatat,dalam_pembinaan,selesai,panggilan_ortu'],
        ]);

        $type = ViolationType::findOrFail($validated['violation_type_id']);
        $student = Student::findOrFail($validated['student_id']);

        $violation = Violation::create([
            'student_id' => $validated['student_id'],
            'violation_type_id' => $validated['violation_type_id'],
            'incident_date' => $validated['incident_date'],
            'incident_time' => $validated['incident_time'],
            'location' => $validated['location'],
            'chronology' => $validated['chronology'],
            'follow_up_action' => $validated['follow_up_action'] ?? $type->default_action,
            'recorded_by_user_id' => auth()->id(),
            'points' => $type->points,
            'status' => $validated['status'],
        ]);

        AuditLog::record('record_violation', "Mencatat pelanggaran untuk siswa {$student->name}: {$type->name} (+{$type->points} poin)");

        // If status requires summons, offer redirect or notification
        if ($validated['status'] === 'panggilan_ortu') {
            return redirect()->route('kedisiplinan.summons.create', ['violation_id' => $violation->id])
                ->with('success', "Pelanggaran {$student->name} dicatat. Silakan lengkapi Surat Panggilan Orang Tua.");
        }

        return back()->with('success', "Pelanggaran untuk {$student->name} berhasil dicatat.");
    }

    // ==========================================
    // 3. SURAT PANGGILAN ORANG TUA
    // ==========================================

    public function summonsIndex(Request $request): View
    {
        $query = ParentSummon::with(['student.schoolClass.homeroomTeacher', 'violation.violationType', 'createdByUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $summons = $query->orderBy('meeting_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('kedisiplinan.summons.index', compact('summons'));
    }

    public function summonCreate(Request $request): View
    {
        $selectedViolation = null;
        $student = null;

        if ($request->filled('violation_id')) {
            $selectedViolation = Violation::with(['student.schoolClass.homeroomTeacher', 'violationType'])->find($request->violation_id);
            if ($selectedViolation) {
                $student = $selectedViolation->student;
            }
        }

        $students = Student::with(['schoolClass.homeroomTeacher'])->orderBy('name')->get();
        $letterNumber = ParentSummon::generateLetterNumber();
        $settings = SystemSetting::current();

        return view('kedisiplinan.summons.create', compact('students', 'selectedViolation', 'student', 'letterNumber', 'settings'));
    }

    public function summonStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'letter_number' => ['required', 'string', 'max:50', 'unique:parent_summons,letter_number'],
            'student_id' => ['required', 'exists:students,id'],
            'violation_id' => ['nullable', 'exists:violations,id'],
            'reason' => ['required', 'string'],
            'meeting_date' => ['required', 'date'],
            'meeting_time' => ['required'],
            'meeting_place' => ['required', 'string', 'max:255'],
            'recipient_parent_name' => ['nullable', 'string', 'max:255'],
            'acknowledged_by_title' => ['required', 'string', 'max:100'],
            'acknowledged_by_name' => ['required', 'string', 'max:255'],
            'acknowledged_by_nip' => ['nullable', 'string', 'max:30'],
            'homeroom_teacher_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,dikirim,hadir,tidak_hadir'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by_user_id'] = auth()->id();

        $summon = ParentSummon::create($validated);

        if ($summon->violation_id) {
            Violation::where('id', $summon->violation_id)->update(['status' => 'panggilan_ortu']);
        }

        AuditLog::record('issue_parent_summons', "Menerbitkan surat panggilan orang tua no {$summon->letter_number} untuk siswa ID: {$summon->student_id}");

        return redirect()->route('kedisiplinan.summons.print', $summon->id)
            ->with('success', 'Surat panggilan berhasil dibuat dan siap dicetak.');
    }

    public function summonPrint(ParentSummon $summon): View
    {
        $summon->load(['student.schoolClass.homeroomTeacher', 'violation.violationType']);
        $settings = SystemSetting::current();

        return view('kedisiplinan.summons.print', compact('summon', 'settings'));
    }

    public function summonUpdateStatus(Request $request, ParentSummon $summon): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,dikirim,hadir,tidak_hadir'],
            'notes' => ['nullable', 'string'],
        ]);

        $summon->update($validated);
        AuditLog::record('update_parent_summons_status', "Status surat panggilan {$summon->letter_number} diubah menjadi {$summon->status}");

        return back()->with('success', "Status surat panggilan orang tua berhasil diubah menjadi: {$summon->status}");
    }

    // ==========================================
    // 4. EKSPOR LAPORAN PELANGGARAN
    // ==========================================

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Violation::with(['student.schoolClass', 'violationType', 'recordedByUser']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('incident_date', [$request->start_date, $request->end_date]);
        }

        $violations = $query->orderBy('incident_date', 'desc')->get();

        $headers = ['Tanggal Kejadian', 'Waktu', 'NIS Siswa', 'Nama Siswa', 'Kelas', 'Jenis Pelanggaran', 'Kategori', 'Poin', 'Lokasi', 'Kronologi', 'Tindak Lanjut', 'Status', 'Petugas'];
        $rows = [];

        foreach ($violations as $v) {
            $rows[] = [
                $v->incident_date->format('Y-m-d'),
                $v->incident_time ?? '-',
                $v->student->nis ?? '',
                $v->student->name ?? '',
                $v->student->schoolClass->name ?? '',
                $v->violationType->name ?? '',
                $v->violationType->category ?? '',
                $v->points,
                $v->location ?? '',
                $v->chronology,
                $v->follow_up_action ?? '',
                $v->status,
                $v->recordedByUser->name ?? '',
            ];
        }

        AuditLog::record('export_violations', 'Mengekspor laporan pelanggaran ke CSV');

        return CsvService::downloadCsv('laporan_pelanggaran_smanli_'.date('Ymd_His').'.csv', $headers, $rows);
    }
}
