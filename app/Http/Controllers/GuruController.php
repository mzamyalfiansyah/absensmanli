<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\SystemSetting;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\ClassConditionReport;

class GuruController extends Controller
{
    /**
     * Determine whether the authenticated account is a Super Admin.
     * This avoids Intelephense errors on calls to $user->isSuperAdmin().
     */
    private function isSuperAdminAccount(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && in_array($user->role, ['super_admin', 'superadmin'], true);
    }

    private function getTeacher(): ?Teacher
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        if ($user->teacher) {
            return $user->teacher;
        }


        if ($this->isSuperAdminAccount()) {
            return Teacher::first();
        }

        return null;
    }

    public function schedules(Request $request): View
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403, 'Akun Anda tidak terautentikasi dengan benar.');
        }

        $isSuperAdmin = $this->isSuperAdminAccount();
        $teacher = $user->teacher;

        if (! $isSuperAdmin && ! $teacher) {
            abort(403, 'Akun Anda belum terdaftar sebagai data Guru.');
        }

        if ($isSuperAdmin) {
            $request->validate([
                'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
                'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
                'class_id'   => ['nullable', 'integer', 'exists:classes,id'],
                'day'        => ['nullable', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            ]);
        }

        $query = Schedule::with([
            'teacher',
            'schoolClass',
            'subject',
            'academicYear',
        ]);

        if ($isSuperAdmin) {
            $query
                ->when($request->filled('teacher_id'), function ($q) use ($request) {
                    $q->where('teacher_id', $request->input('teacher_id'));
                })
                ->when($request->filled('subject_id'), function ($q) use ($request) {
                    $q->where('subject_id', $request->input('subject_id'));
                })
                ->when($request->filled('class_id'), function ($q) use ($request) {
                    $q->where('class_id', $request->input('class_id'));
                })
                ->when($request->filled('day'), function ($q) use ($request) {
                    $q->where('day_of_week', $request->input('day'));
                });

            $teachers = Teacher::orderBy('name')->get();
            $subjects = Subject::orderBy('name')->get();
            $classes = SchoolClass::orderBy('name')->get();
        } else {
            $query->where('teacher_id', $teacher->id);

            $teachers = collect();
            $subjects = collect();
            $classes = collect();
        }

        $schedules = $query
            ->orderByRaw("FIELD(day_of_week, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('guru.schedules', compact(
            'teacher',
            'schedules',
            'isSuperAdmin',
            'teachers',
            'subjects',
            'classes'
        ));
    }

    public function openSession(Request $request, Schedule $schedule): RedirectResponse
    {
        $isSuperAdmin = $this->isSuperAdminAccount();
        $currentTeacher = Auth::user() instanceof User ? Auth::user()->teacher : null;

        if (! $isSuperAdmin && (! $currentTeacher || (int) $schedule->teacher_id !== (int) $currentTeacher->id)) {
            abort(403, 'Jadwal ini bukan jadwal mengajar Anda.');
        }

        $teacher = $isSuperAdmin ? $schedule->teacher : $currentTeacher;

        if (! $teacher) {
            abort(403, 'Jadwal ini belum memiliki data guru yang valid.');
        }

        $schedule->loadMissing(['subject', 'schoolClass']);
        $settings = SystemSetting::current();
        $duration = (int) $request->input('duration', $settings->default_session_minutes);
        $title = $request->input('title', 'Sesi '.$schedule->subject->name.' - '.$schedule->schoolClass->name);

        $session = AttendanceSession::createNewSession($schedule, $teacher, $duration, $title);

        AuditLog::record(
            'open_session',
            "Guru {$teacher->name} membuka sesi absensi QR untuk {$schedule->schoolClass->name} (durasi: {$duration}m)"
        );

        return redirect()->route('guru.sessions.show', $session->id)
            ->with('success', 'Sesi absensi berhasil dibuka dengan QR Code dinamis.');
    }

    public function showSession(AttendanceSession $session): View
    {
        $isSuperAdmin = $this->isSuperAdminAccount();
        $currentTeacher = Auth::user() instanceof User ? Auth::user()->teacher : null;

        if (! $isSuperAdmin && (! $currentTeacher || (int) $session->teacher_id !== (int) $currentTeacher->id)) {
            abort(403, 'Akses sesi ditolak.');
        }

        $session->isOpen();
        $session->load(['schedule.schoolClass', 'schedule.subject']);
        $class = $session->schedule->schoolClass;
        $qrCodeDataUri = QrCodeService::generateDataUri($session->token, 10);
        $students = Student::where('class_id', $class->id)->orderBy('name')->get();

        $attendances = StudentAttendance::where('schedule_id', $session->schedule_id)
            ->where('date', $session->date)
            ->get()
            ->keyBy('student_id');

        $scannedList = [];
        $unscannedList = [];

        foreach ($students as $student) {
            if ($attendances->has($student->id)) {
                $scannedList[] = [
                    'student' => $student,
                    'attendance' => $attendances[$student->id],
                ];
            } else {
                $unscannedList[] = $student;
            }
        }

        return view('guru.session_active', compact(
            'session',
            'qrCodeDataUri',
            'scannedList',
            'unscannedList',
            'class'
        ));
    }

    public function closeSession(AttendanceSession $session): RedirectResponse
    {
        $isSuperAdmin = $this->isSuperAdminAccount();
        $currentTeacher = Auth::user() instanceof User ? Auth::user()->teacher : null;

        if (! $isSuperAdmin && (! $currentTeacher || (int) $session->teacher_id !== (int) $currentTeacher->id)) {
            abort(403, 'Akses sesi ditolak.');
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => Carbon::now(),
        ]);

        AuditLog::record('close_session', "Sesi absensi ID {$session->id} ditutup oleh akun yang berwenang.");

        return redirect()->route('guru.sessions.show', $session->id)
            ->with('info', 'Sesi absensi telah berhasil ditutup.');
    }

    public function manualAttendance(Request $request, AttendanceSession $session): RedirectResponse
    {
        $isSuperAdmin = $this->isSuperAdminAccount();
        $currentTeacher = Auth::user() instanceof User ? Auth::user()->teacher : null;

        if (! $isSuperAdmin && (! $currentTeacher || (int) $session->teacher_id !== (int) $currentTeacher->id)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpa'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        StudentAttendance::updateOrCreate(
            [
                'schedule_id' => $session->schedule_id,
                'student_id' => $validated['student_id'],
                'date' => $session->date,
            ],
            [
                'attendance_session_id' => $session->id,
                'status' => $validated['status'],
                'location_status' => 'bypassed',
                'notes' => 'Diinput manual oleh Guru: '.($validated['notes'] ?? '-'),
                'scanned_at' => Carbon::now(),
            ]
        );

        return back()->with('success', 'Status absensi siswa berhasil diperbarui manual.');
    }

    public function teacherSelfCheckIn(Request $request, Schedule $schedule): RedirectResponse
    {
        $isSuperAdmin = $this->isSuperAdminAccount();
        $currentTeacher = Auth::user() instanceof User ? Auth::user()->teacher : null;

        if (! $isSuperAdmin && (! $currentTeacher || (int) $schedule->teacher_id !== (int) $currentTeacher->id)) {
            abort(403, 'Jadwal ini bukan jadwal mengajar Anda.');
        }

        $teacher = $isSuperAdmin ? $schedule->teacher : $currentTeacher;

        if (! $teacher) {
            abort(403, 'Jadwal ini belum memiliki data guru yang valid.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,terlambat,tidak_hadir'],
            'check_in_time' => ['nullable'],
            'has_task' => ['boolean'],
            'task_description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $today = Carbon::today()->toDateString();
        $hasTask = $request->boolean('has_task');

        TeacherAttendance::updateOrCreate(
            [
                'schedule_id' => $schedule->id,
                'teacher_id' => $teacher->id,
                'date' => $today,
            ],
            [
                'status' => $validated['status'],
                'check_in_time' => $validated['check_in_time'] ?: Carbon::now()->format('H:i:s'),
                'verification_method' => 'self_check_in',
                'has_task' => $hasTask,
                'task_description' => $validated['task_description'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        AuditLog::record(
            'teacher_check_in',
            "Guru {$teacher->name} melakukan verifikasi kehadiran ({$validated['status']}) untuk jadwal {$schedule->subject->name}"
        );

        return back()->with('success', 'Kehadiran mengajar berhasil diverifikasi.');
    }

    public function myHistory(Request $request): View
    {
        $teacher = $this->getTeacher();

        if (! $teacher) {
            abort(403, 'Akun Anda bukan data Guru.');
        }

        $attendances = TeacherAttendance::with([
            'schedule.schoolClass',
            'schedule.subject',
            'verifiedByUser',
        ])
            ->where('teacher_id', $teacher->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('guru.history', compact('teacher', 'attendances'));
    }


    public function classAttendance(Request $request, Schedule $schedule): View
{
    $isSuperAdmin = $this->isSuperAdminAccount();
    $currentTeacher = Auth::user() instanceof User ? Auth::user()->teacher : null;

    if (! $isSuperAdmin && (! $currentTeacher || (int) $schedule->teacher_id !== (int) $currentTeacher->id)) {
        abort(403, 'Jadwal ini bukan jadwal mengajar Anda.');
    }

    $schedule->loadMissing(['schoolClass', 'subject']);
    $date = $request->input('date', Carbon::today()->toDateString());

    $students = Student::where('class_id', $schedule->class_id)->orderBy('name')->get();

    $attendances = StudentAttendance::where('schedule_id', $schedule->id)
        ->whereDate('date', $date)
        ->get()
        ->keyBy('student_id');

    $summary = [
        'hadir'     => $attendances->whereIn('status', ['hadir', 'terlambat'])->count(),
        'izin'      => $attendances->where('status', 'izin')->count(),
        'sakit'     => $attendances->where('status', 'sakit')->count(),
        'alpa'      => $attendances->where('status', 'alpa')->count(),
        'belum'     => $students->count() - $attendances->count(),
    ];

    $report = ClassConditionReport::where('schedule_id', $schedule->id)
        ->whereDate('date', $date)
        ->first();

    return view('guru.class_attendance', compact('schedule', 'date', 'students', 'attendances', 'summary', 'report'));
}
}