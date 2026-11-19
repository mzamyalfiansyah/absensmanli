<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\ClassConditionReport;
use App\Models\ParentSummon;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dispatch user to role-specific dashboard.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role) {
            'superadmin' => $this->superAdminDashboard(),
            'admin_kedisiplinan' => $this->adminKedisiplinanDashboard(),
            'admin_kurikulum' => $this->adminKurikulumDashboard(),
            'admin_walas' => $this->adminWalasDashboard(),
            'admin_kelas' => $this->adminKelasDashboard(),
            'guru' => $this->guruDashboard(),
            'siswa' => $this->siswaDashboard(),
            default => abort(403, 'Role pengguna tidak dikenali.'),
        };
    }

    /**
     * Super Admin Dashboard.
     */
    private function superAdminDashboard(): View
    {
        $today = Carbon::today()->toDateString();

        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_classes' => SchoolClass::count(),
            'active_academic_year' => AcademicYear::getActive(),
            'today_student_attendances' => StudentAttendance::where('date', $today)->count(),
            'today_teacher_attendances' => TeacherAttendance::where('date', $today)->count(),
            'pending_condition_reports' => ClassConditionReport::where('status', 'pending')->count(),
            'total_violations' => Violation::count(),
        ];

        $recentLogs = AuditLog::with('user')->orderBy('id', 'desc')->limit(8)->get();
        $recentSessions = AttendanceSession::with(['schedule.schoolClass', 'schedule.subject', 'teacher'])
            ->where('date', $today)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('dashboards.superadmin', compact('stats', 'recentLogs', 'recentSessions'));
    }

    /**
     * Admin Kedisiplinan Dashboard.
     */
    private function adminKedisiplinanDashboard(): View
    {
        $today = Carbon::today()->toDateString();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $stats = [
            'total_violations' => Violation::count(),
            'today_violations' => Violation::where('incident_date', $today)->count(),
            'month_violations' => Violation::whereYear('incident_date', $thisYear)->whereMonth('incident_date', $thisMonth)->count(),
            'pending_followups' => Violation::whereIn('status', ['tercatat', 'dalam_pembinaan'])->count(),
            'total_summons' => ParentSummon::count(),
            'active_summons' => ParentSummon::whereIn('status', ['draft', 'dikirim'])->count(),
        ];

        $recentViolations = Violation::with(['student.schoolClass', 'violationType', 'recordedByUser'])
            ->orderBy('incident_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $topStudentsWithViolations = Student::with('schoolClass')
            ->withCount(['violations as points_sum' => function ($query) {
                $query->select(\DB::raw('COALESCE(SUM(points), 0)'));
            }])
            ->having('points_sum', '>', 0)
            ->orderBy('points_sum', 'desc')
            ->limit(5)
            ->get();

        return view('dashboards.kedisiplinan', compact('stats', 'recentViolations', 'topStudentsWithViolations'));
    }

    /**
     * Admin Kurikulum Dashboard.
     */
    private function adminKurikulumDashboard(): View
    {
        $today = Carbon::today()->toDateString();
        $dayOfWeek = strtolower(Carbon::now()->translatedFormat('l'));
        // Map Indonesian day names
        $dayMap = [
            'monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu',
            'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu', 'sunday' => 'minggu',
            'senin' => 'senin', 'selasa' => 'selasa', 'rabu' => 'rabu',
            'kamis' => 'kamis', 'jumat' => 'jumat', 'sabtu' => 'sabtu', 'minggu' => 'minggu',
        ];
        $currentDay = $dayMap[strtolower(Carbon::now()->format('l'))] ?? 'senin';

        $todaySchedules = Schedule::with(['schoolClass', 'subject', 'teacher'])
            ->where('day_of_week', $currentDay)
            ->orderBy('start_time')
            ->get();

        $todayTeacherAttendances = TeacherAttendance::where('date', $today)->get()->keyBy('schedule_id');

        $stats = [
            'today_schedules_count' => $todaySchedules->count(),
            'teacher_hadir_count' => $todayTeacherAttendances->where('status', 'hadir')->count(),
            'teacher_terlambat_count' => $todayTeacherAttendances->where('status', 'terlambat')->count(),
            'teacher_tidak_hadir_count' => $todayTeacherAttendances->where('status', 'tidak_hadir')->count(),
            'absent_with_task' => $todayTeacherAttendances->where('status', 'tidak_hadir')->where('has_task', true)->count(),
            'absent_without_task' => $todayTeacherAttendances->where('status', 'tidak_hadir')->where('has_task', false)->count(),
            'pending_class_reports' => ClassConditionReport::where('status', 'pending')->count(),
        ];

        $pendingReports = ClassConditionReport::with(['schoolClass', 'teacher', 'schedule.subject', 'submittedByUser'])
            ->where('status', 'pending')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('dashboards.kurikulum', compact('stats', 'todaySchedules', 'todayTeacherAttendances', 'pendingReports', 'currentDay'));
    }

    /**
     * Admin Walas Dashboard.
     */
    private function adminWalasDashboard(): View
    {
        $user = Auth::user();

        $homeroomClass = SchoolClass::with(['academicYear', 'homeroomTeacher'])
            ->homeroomOf($user)
            ->first();

        // If user is superadmin viewing walas dashboard or fallback
        if (! $homeroomClass && $user->isSuperAdmin()) {
            $homeroomClass = SchoolClass::with(['academicYear', 'homeroomTeacher'])->first();
        }


        $today = Carbon::today()->toDateString();
        $stats = [
            'total_students' => 0,
            'hadir_today' => 0,
            'sakit_today' => 0,
            'izin_today' => 0,
            'alpa_today' => 0,
            'total_violations' => 0,
        ];
        $students = collect();
        $recentViolations = collect();

        if ($homeroomClass) {
            $students = Student::where('class_id', $homeroomClass->id)->orderBy('name')->get();
            $studentIds = $students->pluck('id');

            $stats['total_students'] = $students->count();

            $todayAttendances = StudentAttendance::whereIn('student_id', $studentIds)
                ->where('date', $today)
                ->get();

            $stats['hadir_today'] = $todayAttendances->where('status', 'hadir')->count();
            $stats['sakit_today'] = $todayAttendances->where('status', 'sakit')->count();
            $stats['izin_today'] = $todayAttendances->where('status', 'izin')->count();
            $stats['alpa_today'] = $todayAttendances->where('status', 'alpa')->count();

            $stats['total_violations'] = Violation::whereIn('student_id', $studentIds)->count();

            $recentViolations = Violation::with(['student', 'violationType'])
                ->whereIn('student_id', $studentIds)
                ->orderBy('incident_date', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboards.walas', compact('homeroomClass', 'stats', 'students', 'recentViolations'));
    }

    /**
     * Admin Kelas Dashboard.
     */
    private function adminKelasDashboard(): View
    {
        $user = Auth::user();
        $schoolClass = SchoolClass::with(['homeroomTeacher', 'academicYear'])
            ->where('class_admin_user_id', $user->id)
            ->first();

        // If superadmin or fallback
        if (! $schoolClass && $user->isSuperAdmin()) {
            $schoolClass = SchoolClass::with(['homeroomTeacher', 'academicYear'])->first();
        }

        $today = Carbon::today()->toDateString();
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $currentDay = $dayMap[Carbon::now()->format('l')] ?? 'senin';

        $schedules = collect();
        $reportsToday = collect();

        if ($schoolClass) {
            $schedules = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $schoolClass->id)
                ->where('day_of_week', $currentDay)
                ->orderBy('start_time')
                ->get();

            $reportsToday = ClassConditionReport::with('teacher')
                ->where('class_id', $schoolClass->id)
                ->where('date', $today)
                ->get()
                ->keyBy('schedule_id');
        }

        return view('dashboards.kelas', compact('schoolClass', 'schedules', 'reportsToday', 'currentDay'));
    }

    /**
     * Guru Dashboard.
     */
    private function guruDashboard(): View
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (! $teacher && $user->isSuperAdmin()) {
            $teacher = Teacher::first();
        }

        $today = Carbon::today()->toDateString();
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $currentDay = $dayMap[Carbon::now()->format('l')] ?? 'senin';

        $todaySchedules = collect();
        $activeSessions = collect();
        $myAttendancesToday = collect();

        if ($teacher) {
            $todaySchedules = Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day_of_week', $currentDay)
                ->orderBy('start_time')
                ->get();

            $activeSessions = AttendanceSession::with('schedule.schoolClass')
                ->where('teacher_id', $teacher->id)
                ->where('date', $today)
                ->get()
                ->keyBy('schedule_id');

            $myAttendancesToday = TeacherAttendance::where('teacher_id', $teacher->id)
                ->where('date', $today)
                ->get()
                ->keyBy('schedule_id');
        }

        return view('dashboards.guru', compact('teacher', 'todaySchedules', 'activeSessions', 'myAttendancesToday', 'currentDay'));
    }

    /**
     * Siswa Dashboard.
     */
    private function siswaDashboard(): View
    {
        $user = Auth::user();
        $student = $user->student ? $user->student->load('schoolClass') : null;

        if (! $student && $user->isSuperAdmin()) {
            $student = Student::with('schoolClass')->first();
        }

        $today = Carbon::today()->toDateString();
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $currentDay = $dayMap[Carbon::now()->format('l')] ?? 'senin';

        $todaySchedules = collect();
        $myAttendancesToday = collect();
        $activeSessions = collect();
        $stats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total_points' => 0,
        ];
        $violations = collect();

        if ($student) {
            $todaySchedules = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $student->class_id)
                ->where('day_of_week', $currentDay)
                ->orderBy('start_time')
                ->get();

            $myAttendancesToday = StudentAttendance::where('student_id', $student->id)
                ->where('date', $today)
                ->get()
                ->keyBy('schedule_id');

            // Find active open sessions for their schedules today
            $activeSessions = AttendanceSession::whereIn('schedule_id', $todaySchedules->pluck('id'))
                ->where('date', $today)
                ->where('status', 'open')
                ->where('expires_at', '>', Carbon::now())
                ->get()
                ->keyBy('schedule_id');

            // Aggregate overall attendance stats
            $allMyAttendances = StudentAttendance::where('student_id', $student->id)->get();
            $stats['hadir'] = $allMyAttendances->where('status', 'hadir')->count();
            $stats['terlambat'] = $allMyAttendances->where('status', 'terlambat')->count();
            $stats['izin'] = $allMyAttendances->where('status', 'izin')->count();
            $stats['sakit'] = $allMyAttendances->where('status', 'sakit')->count();
            $stats['alpa'] = $allMyAttendances->where('status', 'alpa')->count();

            // Violations allowed for student viewing
            $violations = Violation::with('violationType')
                ->where('student_id', $student->id)
                ->orderBy('incident_date', 'desc')
                ->get();

            $stats['total_points'] = $violations->sum('points');
        }

        return view('dashboards.siswa', compact('student', 'todaySchedules', 'myAttendancesToday', 'activeSessions', 'stats', 'violations', 'currentDay'));
    }
}