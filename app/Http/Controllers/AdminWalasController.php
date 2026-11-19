<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\Violation;
use App\Services\CsvService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassConditionReport;


class AdminWalasController extends Controller
{
    
    private function getHomeroomClass(): ?SchoolClass
    {
        $user = Auth::user();

        $class = SchoolClass::homeroomOf($user)->first();
        if ($class) {
            return $class;
        }

        if ($user->role === 'superadmin') {
            return SchoolClass::first();
        }

        return null;
    }

    /**
     * - SuperAdmin : semua kelas (bisa difilter lewat dropdown kelas)
     * - Wali kelas : hanya kelas dengan homeroom_teacher_id = teacher miliknya
     */
    private function attendanceContext(Request $request): array
    {
        $user    = Auth::user();
        $isAdmin = $user->role === 'superadmin';

        $classQuery = SchoolClass::orderBy('level')->orderBy('name');

        if (! $isAdmin) {
            $classQuery->homeroomOf($user);
        }

        $classes = $classQuery->get();
        if ($classes->isEmpty()) {
            abort(403, 'Anda belum ditugaskan sebagai Wali Kelas pada kelas manapun.');
        }

        $classNames = $classes->pluck('name', 'id')->all();

        $requestedClass  = (int) $request->input('class_id');
        $selectedClassId = $classes->pluck('id')->contains($requestedClass) ? $requestedClass : null;
        $classIds        = $selectedClassId ? [$selectedClassId] : $classes->pluck('id')->all();

        $class = $selectedClassId
            ? $classes->firstWhere('id', $selectedClassId)
            : ($classes->count() === 1 ? $classes->first() : null);

        $homeroom = ($class && $class->homeroom_teacher_id)
            ? Teacher::find($class->homeroom_teacher_id)
            : null;

        $students = Student::whereIn('class_id', $classIds)->orderBy('name')->get();

        $requestedStudent = (int) $request->input('student_id');
        $studentId        = $students->pluck('id')->contains($requestedStudent) ? $requestedStudent : null;

        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::today()->toDateString());

        return compact(
            'isAdmin', 'classes', 'classNames', 'class', 'homeroom', 'classIds',
            'selectedClassId', 'students', 'studentId', 'startDate', 'endDate'
        );
    }

    private function attendanceQuery(array $ctx)
    {
        $studentIds = $ctx['studentId'] ? [$ctx['studentId']] : $ctx['students']->pluck('id')->all();

        return StudentAttendance::query()
            ->whereIn('student_id', $studentIds)
            ->whereBetween('date', [$ctx['startDate'], $ctx['endDate']]);
    }

    public function attendanceRecap(Request $request): View
    {
        $ctx   = $this->attendanceContext($request);
        $query = $this->attendanceQuery($ctx);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $counts = (clone $query)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'hadir'     => (int) ($counts['hadir'] ?? 0),
            'terlambat' => (int) ($counts['terlambat'] ?? 0),
            'izin'      => (int) ($counts['izin'] ?? 0),
            'sakit'     => (int) ($counts['sakit'] ?? 0),
            'alpa'      => (int) ($counts['alpa'] ?? 0),
        ];

        $total      = array_sum($summary);
        $percentage = $total > 0
            ? round((($summary['hadir'] + $summary['terlambat']) / $total) * 100, 1)
            : 0;

        $attendances = (clone $query)
            ->with(['student', 'schedule.subject'])
            ->orderBy('date', 'desc')
            ->orderBy('scanned_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('walas.attendance', array_merge($ctx, compact('summary', 'percentage', 'attendances')));
    }

    
    public function violationsIndex(Request $request): View
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'superadmin';

        $classes = SchoolClass::query()
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $class = null;

        if ($isAdmin) {
            $requestedClassId = $request->input('class_id');

            $selectedClassId = null;

            if (
                $requestedClassId !== null
                && $requestedClassId !== ''
                && ctype_digit((string) $requestedClassId)
                && $classes->contains('id', (int) $requestedClassId)
            ) {
                $selectedClassId = (int) $requestedClassId;
            }

            if ($selectedClassId !== null) {
                $class = $classes->firstWhere('id', $selectedClassId);
                $classIds = [$selectedClassId];
            } else {
                $classIds = $classes->pluck('id')->all();
            }
        } else {
            $class = SchoolClass::homeroomOf($user)->first();

            if (! $class) {
                abort(403, 'Anda belum ditugaskan sebagai Wali Kelas.');
            }

            $classIds = [$class->id];
        }

        $students = Student::whereIn('class_id', $classIds)
            ->orderBy('name')
            ->get();

        $studentIds = $students->pluck('id');

        $query = Violation::with([
            'student',
            'violationType',
            'recordedByUser',
        ])->whereIn('student_id', $studentIds);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $violations = $query
            ->orderByDesc('incident_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('walas.violations', compact(
            'class',
            'classes',
            'isAdmin',
            'students',
            'violations'
        ));
    }


    public function printRecap(Request $request): View
    {
        $ctx = $this->attendanceContext($request);

        $attendances = $this->attendanceQuery($ctx)
            ->with(['student', 'schedule.subject'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $summary = [];
        foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpa'] as $s) {
            $summary[$s] = $attendances->where('status', $s)->count();
        }

        return view('walas.print', array_merge($ctx, compact('attendances', 'summary')));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $ctx = $this->attendanceContext($request);

        $records = $this->attendanceQuery($ctx)
            ->with(['student', 'schedule.subject'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $headers = ['Tanggal', 'NIS Siswa', 'Nama Siswa', 'Kelas', 'Mata Pelajaran', 'Status', 'Waktu Scan', 'Status Lokasi', 'Jarak (Meter)'];
        $rows = [];

        foreach ($records as $r) {
            $rows[] = [
                $r->date->format('Y-m-d'),
                $r->student->nis ?? '',
                $r->student->name ?? '',
                $ctx['classNames'][$r->student->class_id ?? 0] ?? '',
                $r->schedule->subject->name ?? '',
                $r->status,
                $r->scanned_at ? $r->scanned_at->format('H:i:s') : '-',
                $r->location_status,
                $r->distance_meters ?? '-',
            ];
        }

        $label = $ctx['class'] ? $ctx['class']->name : 'semua_kelas';

        AuditLog::record('export_walas_recap', "Mengekspor rekap presensi {$label} ke CSV");

        return CsvService::downloadCsv(
            "rekap_kelas_{$label}_{$ctx['startDate']}_{$ctx['endDate']}.csv",
            $headers,
            $rows
        );
    }

    public function classReports(Request $request): View
    {
        $class = $this->getHomeroomClass();
        if (! $class) {
            abort(403, 'Anda belum ditugaskan sebagai Wali Kelas pada kelas manapun.');
        }

        $date = $request->input('date', Carbon::today()->toDateString());

        $reports = ClassConditionReport::with(['schedule.subject', 'teacher', 'submittedByUser'])
            ->where('class_id', $class->id)
            ->whereDate('date', $date)
            ->get()
            ->sortBy(fn ($r) => $r->schedule->start_time)
            ->values();

        $absences = StudentAttendance::with('student')
            ->whereHas('student', fn ($q) => $q->where('class_id', $class->id))
            ->whereDate('date', $date)
            ->whereIn('status', ['izin', 'sakit', 'alpa'])
            ->get()
            ->groupBy('schedule_id');

        return view('walas.class_reports', compact('class', 'date', 'reports', 'absences'));
    }
}