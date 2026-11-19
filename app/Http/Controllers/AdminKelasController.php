<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClassConditionReport;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AdminKelasController extends Controller
{
    private function getAssignedClass(): ?SchoolClass
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $class = SchoolClass::where('class_admin_user_id', $user->id)->first();

        // Fallback untuk SuperAdmin saat testing
        if (! $class && $user->isSuperAdmin()) {
            return SchoolClass::first();
        }

        return $class;
    }

    public function index(): View
    {
        $class = $this->getAssignedClass();
        if (! $class) {
            abort(403, 'Akun Anda belum ditautkan ke kelas manapun sebagai Admin Kelas.');
        }

        $today = Carbon::today()->toDateString();
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $currentDay = $dayMap[Carbon::now()->format('l')] ?? 'senin';

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('class_id', $class->id)
            ->where('day_of_week', $currentDay)
            ->orderBy('start_time')
            ->get();

        $todayReports = ClassConditionReport::with('teacher')
            ->where('class_id', $class->id)
            ->where('date', $today)
            ->get()
            ->keyBy('schedule_id');

        $students = Student::where('class_id', $class->id)->orderBy('name')->get();

        $todayAbsences = StudentAttendance::with('student')
            ->whereIn('schedule_id', $schedules->pluck('id'))
            ->whereDate('date', $today)
            ->whereIn('status', ['izin', 'sakit', 'alpa'])
            ->get()
            ->groupBy('schedule_id');

        return view('kelas.index', compact('class', 'schedules', 'todayReports', 'students', 'currentDay', 'today', 'todayAbsences'));

        //return view('kelas.index', compact('class', 'schedules', 'todayReports', 'students', 'currentDay', 'today'));
    }

    public function createReport(Schedule $schedule): View
    {
        $class = $this->getAssignedClass();
        if (! $class || $schedule->class_id !== $class->id) {
            abort(403, 'Jadwal ini bukan milik kelas Anda.');
        }

        $today = Carbon::today()->toDateString();
        $existingReport = ClassConditionReport::where('schedule_id', $schedule->id)
            ->where('date', $today)
            ->first();

        $students = Student::where('class_id', $class->id)->orderBy('name')->get();

        return view('kelas.report_form', compact('schedule', 'class', 'existingReport', 'students', 'today'));
    }

    public function storeReport(Request $request, Schedule $schedule): RedirectResponse
    {
        $class = $this->getAssignedClass();
        if (! $class || $schedule->class_id !== $class->id) {
            abort(403, 'Jadwal ini bukan milik kelas Anda.');
        }

        $validated = $request->validate([
            'teacher_status' => ['required', 'in:hadir,terlambat,tidak_hadir'],
            'teacher_arrival_time' => ['nullable'],
            'has_task' => ['boolean'],
            'task_description' => ['nullable', 'string'],
            'absent_students_notes' => ['nullable', 'string'],
            'class_condition_notes' => ['nullable', 'string'],
        ]);

        $today = Carbon::today()->toDateString();
        $hasTask = $request->boolean('has_task');

        $report = ClassConditionReport::updateOrCreate(
            [
                'schedule_id' => $schedule->id,
                'class_id' => $class->id,
                'date' => $today,
            ],
            [
                'submitted_by_user_id' => Auth::user(),
                'period_label' => $schedule->period_label,
                'teacher_id' => $schedule->teacher_id,
                'teacher_status' => $validated['teacher_status'],
                'teacher_arrival_time' => $validated['teacher_arrival_time'] ?? null,
                'has_task' => $hasTask,
                'task_description' => $validated['task_description'] ?? null,
                'absent_students_notes' => $validated['absent_students_notes'] ?? null,
                'class_condition_notes' => $validated['class_condition_notes'] ?? null,
                'status' => 'pending', // Menunggu persetujuan Admin Kurikulum
            ]
        );

        AuditLog::record('submit_class_report', "Admin Kelas mengirim laporan jadwal {$schedule->subject->name} ({$class->name}) - Menunggu verifikasi");

        return redirect()->route('admin_kelas.index')
            ->with('success', 'Laporan kondisi kelas & kehadiran guru berhasil dikirim. Menunggu verifikasi dari Admin Kurikulum.');
    }

    public function recordAbsentStudent(Request $request, Schedule $schedule): RedirectResponse
    {
        $class = $this->getAssignedClass();
        if (! $class || (int) $schedule->class_id !== (int) $class->id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'student_id' => ['required', 'integer'],
            'status'     => ['required', 'in:izin,sakit,alpa'],
            'notes'      => ['nullable', 'string', 'max:255'],
            'attachment' => ['required_if:status,sakit', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'attachment.required_if' => 'Siswa sakit wajib melampirkan surat dokter (JPG/PNG/PDF, maks 2 MB).',
        ]);

        // Hanya siswa di kelas ini
        $student = Student::where('id', $validated['student_id'])
            ->where('class_id', $class->id)
            ->firstOrFail();

        $today = Carbon::today()->toDateString();

        $existing = StudentAttendance::where('schedule_id', $schedule->id)
            ->where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && in_array($existing->status, ['hadir', 'terlambat'], true)
            && $existing->location_status !== 'manual') {
            return back()->with('error', "{$student->name} sudah absen hadir lewat QR, tidak bisa diubah oleh Admin Kelas.");
        }

        $attachmentPath = $existing?->attachment;

        if ($request->hasFile('attachment') && $validated['status'] !== 'alpa') {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }
            $attachmentPath = $request->file('attachment')->store("surat-keterangan/{$class->id}", 'local');
        }

        // Alpa tidak punya surat
        if ($validated['status'] === 'alpa' && $attachmentPath) {
            Storage::disk('local')->delete($attachmentPath);
            $attachmentPath = null;
        }

        StudentAttendance::updateOrCreate(
            [
                'schedule_id' => $schedule->id,
                'student_id'  => $student->id,
                'date'        => $today,
            ],
            [
                'status'          => $validated['status'],
                'location_status' => 'manual',
                'notes'           => 'Diinput oleh Admin Kelas: ' . ($validated['notes'] ?? '-'),
                'attachment'      => $attachmentPath,
            ]
        );

        AuditLog::record('manual_student_attendance', "Admin Kelas mencatat {$student->name} berstatus {$validated['status']}");

        return back()->with('success', "Ketidakhadiran {$student->name} ({$validated['status']}) berhasil dicatat.");
    }
}