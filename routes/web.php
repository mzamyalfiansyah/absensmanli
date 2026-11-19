<?php

use App\Http\Controllers\AdminKedisiplinanController;
use App\Http\Controllers\AdminKelasController;
use App\Http\Controllers\AdminKurikulumController;
use App\Http\Controllers\AdminWalasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceAttachmentController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\TahunAjaranController;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication routes (Guests)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated common routes
Route::middleware(['auth', 'account.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Password change (always accessible even if must_change_password is true)
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Tahun Ajaran (3 tahun ke belakang & ke depan)
    Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun_ajaran.index');
    Route::get('/tahun-ajaran/{tahun}', [TahunAjaranController::class, 'show'])
        ->where('tahun', '[0-9]{4}-[0-9]{4}')
        ->name('tahun_ajaran.show');

    Route::get('/attendance/{attendance}/attachment', [AttendanceAttachmentController::class, 'show'])->name('attendance.attachment');
    // ==========================================
    // 1. SUPERADMIN ROUTES
    // ==========================================
    Route::middleware('role:superadmin')->prefix('admin')->name('admin.')->group(function () {
        // Users & Admins
        Route::get('/users', [SuperAdminController::class, 'usersIndex'])->name('users.index');
        Route::post('/users', [SuperAdminController::class, 'userStore'])->name('users.store');
        Route::put('/users/{user}', [SuperAdminController::class, 'userUpdate'])->name('users.update');
        Route::post('/users/{user}/toggle', [SuperAdminController::class, 'userToggleActive'])->name('users.toggle');
        Route::post('/users/{user}/reset-password', [SuperAdminController::class, 'userResetPassword'])->name('users.reset_password');

        // Master Academic Years
        Route::get('/master/academic-years', [SuperAdminController::class, 'academicYearsIndex'])->name('master.academic_years');
        Route::post('/master/academic-years', [SuperAdminController::class, 'academicYearStore'])->name('master.academic_years.store');
        Route::post('/master/academic-years/{academicYear}/set-active', [SuperAdminController::class, 'academicYearSetActive'])->name('master.academic_years.set_active');

        // Master Classes
        Route::get('/master/classes', [SuperAdminController::class, 'classesIndex'])->name('master.classes');
        Route::post('/master/classes', [SuperAdminController::class, 'classStore'])->name('master.classes.store');
        Route::put('/master/classes/{class}', [SuperAdminController::class, 'classUpdate'])->name('master.classes.update');

        // Master Teachers + CSV
        Route::get('/master/teachers', [SuperAdminController::class, 'teachersIndex'])->name('master.teachers');
        Route::post('/master/teachers', [SuperAdminController::class, 'teacherStore'])->name('master.teachers.store');
        Route::post('/master/teachers/import', [SuperAdminController::class, 'teacherImportCsv'])->name('master.teachers.import');
        Route::get('/master/teachers/export', [SuperAdminController::class, 'teacherExportCsv'])->name('master.teachers.export');

        // Master Students + CSV
        Route::get('/master/students', [SuperAdminController::class, 'studentsIndex'])->name('master.students');
        Route::post('/master/students', [SuperAdminController::class, 'studentStore'])->name('master.students.store');
        Route::post('/master/students/import', [SuperAdminController::class, 'studentImportCsv'])->name('master.students.import');
        Route::get('/master/students/export', [SuperAdminController::class, 'studentExportCsv'])->name('master.students.export');

        // Master Subjects
        Route::get('/master/subjects', [SuperAdminController::class, 'subjectsIndex'])->name('master.subjects');
        Route::post('/master/subjects', [SuperAdminController::class, 'subjectStore'])->name('master.subjects.store');

        // Master Schedules
        Route::get('/master/schedules', [SuperAdminController::class, 'schedulesIndex'])->name('master.schedules');
        Route::post('/master/schedules', [SuperAdminController::class, 'scheduleStore'])->name('master.schedules.store');
                // Edit & Hapus Jadwal
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
        Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        // System Settings & Coordinates
        Route::get('/settings', [SuperAdminController::class, 'settingsIndex'])->name('settings');
        Route::post('/settings', [SuperAdminController::class, 'settingsUpdate'])->name('settings.update');

        // Audit Logs
        Route::get('/audit-logs', [SuperAdminController::class, 'auditLogsIndex'])->name('audit_logs');
    });

    // ==========================================
    // 2. ADMIN KEDISIPLINAN ROUTES
    // ==========================================
    Route::middleware('role:admin_kedisiplinan,superadmin')->prefix('kedisiplinan')->name('kedisiplinan.')->group(function () {
        Route::get('/students/search', [AdminKedisiplinanController::class, 'searchStudents'])->name('students.search');

        Route::get('/types', [AdminKedisiplinanController::class, 'violationTypesIndex'])->name('types.index');
        Route::post('/types', [AdminKedisiplinanController::class, 'violationTypeStore'])->name('types.store');

        Route::get('/violations', [AdminKedisiplinanController::class, 'violationsIndex'])->name('violations.index');
        Route::post('/violations', [AdminKedisiplinanController::class, 'violationStore'])->name('violations.store');
        Route::get('/violations/export', [AdminKedisiplinanController::class, 'exportCsv'])->name('violations.export');

        Route::get('/summons', [AdminKedisiplinanController::class, 'summonsIndex'])->name('summons.index');
        Route::get('/summons/create', [AdminKedisiplinanController::class, 'summonCreate'])->name('summons.create');
        Route::post('/summons', [AdminKedisiplinanController::class, 'summonStore'])->name('summons.store');
        Route::get('/summons/{summon}/print', [AdminKedisiplinanController::class, 'summonPrint'])->name('summons.print');
        Route::post('/summons/{summon}/status', [AdminKedisiplinanController::class, 'summonUpdateStatus'])->name('summons.status');
    });

    // ==========================================
    // 3. ADMIN KURIKULUM ROUTES
    // ==========================================
    Route::middleware('role:admin_kurikulum,superadmin')->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::get('/teacher-monitor', [AdminKurikulumController::class, 'teacherAttendanceMonitor'])->name('teacher_monitor');

        
        // Jadwal Pelajaran (CRUD)
        Route::get('/schedules', [AdminKurikulumController::class, 'schedulesIndex'])->name('schedules.index');
        Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        Route::get('/class-reports', [AdminKurikulumController::class, 'classReportsIndex'])->name('class_reports.index');
        Route::post('/class-reports/{report}/approve', [AdminKurikulumController::class, 'classReportApprove'])->name('class_reports.approve');
        Route::post('/class-reports/{report}/reject', [AdminKurikulumController::class, 'classReportReject'])->name('class_reports.reject');

        Route::get('/recaps/student', [AdminKurikulumController::class, 'studentAttendanceRecap'])->name('recaps.student');
        Route::get('/recaps/monthly', [AdminKurikulumController::class, 'studentAttendanceMonthlyRecap'])->name('recaps.monthly');
        Route::get('/recaps/chart', [AdminKurikulumController::class, 'attendanceChart'])->name('recaps.chart');
        Route::get('/recaps/chart-data', [AdminKurikulumController::class, 'attendanceChartData'])->name('recaps.chart_data');
        Route::get('/recaps/teacher', [AdminKurikulumController::class, 'teacherAttendanceRecap'])->name('recaps.teacher');
        Route::get('/recaps/print', [AdminKurikulumController::class, 'printRecap'])->name('recaps.print');
        Route::get('/recaps/teacher/export', [AdminKurikulumController::class, 'exportTeacherCsv'])->name('recaps.teacher.export');
    });

    // ==========================================
    // 4. ADMIN WALAS ROUTES
    // ==========================================
    Route::middleware('role:admin_walas,superadmin')->prefix('walas')->name('walas.')->group(function () {
        Route::get('/attendance', [AdminWalasController::class, 'attendanceRecap'])->name('attendance');
        Route::get('/attendance/print', [AdminWalasController::class, 'printRecap'])->name('attendance.print');
        Route::get('/attendance/export', [AdminWalasController::class, 'exportCsv'])->name('attendance.export');
        Route::get('/violations', [AdminWalasController::class, 'violationsIndex'])->name('violations');
        Route::get('/class-reports', [AdminWalasController::class, 'classReports'])->name('class_reports');
    });

    // ==========================================
    // 5. ADMIN KELAS ROUTES
    // ==========================================
    Route::middleware('role:admin_kelas,superadmin')->prefix('kelas')->name('admin_kelas.')->group(function () {
        Route::get('/', [AdminKelasController::class, 'index'])->name('index');
        Route::get('/report/{schedule}', [AdminKelasController::class, 'createReport'])->name('report.create');
        Route::post('/report/{schedule}', [AdminKelasController::class, 'storeReport'])->name('report.store');
        Route::post('/absent-student/{schedule}', [AdminKelasController::class, 'recordAbsentStudent'])->name('absent_student');
    });

    // ==========================================
    // 6. GURU ROUTES
    // ==========================================
    Route::middleware('role:guru,superadmin')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/schedules', [GuruController::class, 'schedules'])->name('schedules');
        Route::post('/schedules/{schedule}/open-session', [GuruController::class, 'openSession'])->name('sessions.open');
        Route::get('/sessions/{session}', [GuruController::class, 'showSession'])->name('sessions.show');
        Route::post('/sessions/{session}/close', [GuruController::class, 'closeSession'])->name('sessions.close');
        Route::post('/sessions/{session}/manual-attendance', [GuruController::class, 'manualAttendance'])->name('sessions.manual');
        Route::post('/schedules/{schedule}/check-in', [GuruController::class, 'teacherSelfCheckIn'])->name('checkin');
        Route::get('/history', [GuruController::class, 'myHistory'])->name('history');
        Route::get('/schedules/{schedule}/attendance', [GuruController::class, 'classAttendance'])->name('schedules.attendance');
    });

    // ==========================================
    // 7. SISWA ROUTES
    // ==========================================
    Route::middleware('role:siswa,superadmin')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/scan', [SiswaController::class, 'scanPage'])->name('scan');
        Route::post('/scan/submit', [SiswaController::class, 'submitAttendance'])->name('scan.submit');
        Route::get('/history', [SiswaController::class, 'history'])->name('history');
        Route::get('/violations', [SiswaController::class, 'violations'])->name('violations');
    });
});