<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CsvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuperAdminController extends Controller
{
    // ==========================================
    // 1. PENGELOLAAN PENGGUNA & ADMINISTRATOR
    // ==========================================

    public function usersIndex(Request $request): View
    {
        $query = User::query()->with(['teacher', 'student.schoolClass']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $roles = [
            'superadmin' => 'Super Administrator',
            'admin_kedisiplinan' => 'Admin Kedisiplinan',
            'admin_kurikulum' => 'Admin Kurikulum',
            'admin_walas' => 'Admin Walas',
            'admin_kelas' => 'Admin Kelas',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
        ];

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function userStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:superadmin,admin_kedisiplinan,admin_kurikulum,admin_walas,admin_kelas,guru,siswa'],
            'password' => ['required', 'string', 'min:6'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'must_change_password' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'],
            'is_active' => $request->boolean('is_active', true),
            'must_change_password' => $request->boolean('must_change_password', true),
        ]);

        AuditLog::record('create_user', "Membuat akun {$user->role}: {$user->username} ({$user->name})");

        return back()->with('success', "Akun {$user->name} ({$user->username}) berhasil ditambahkan.");
    }

    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:superadmin,admin_kedisiplinan,admin_kurikulum,admin_walas,admin_kelas,guru,siswa'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'must_change_password' => ['boolean'],
        ]);

        $oldRole = $user->role;
        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone_number' => $validated['phone_number'],
            'is_active' => $request->boolean('is_active', true),
            'must_change_password' => $request->boolean('must_change_password', false),
        ]);

        AuditLog::record('update_user', "Memperbarui akun {$user->username} (role: {$oldRole} -> {$user->role})");

        return back()->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function userToggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);
        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record('toggle_user_status', "Akun {$user->username} {$statusStr}");

        return back()->with('success', "Akun {$user->name} berhasil {$statusStr}.");
    }

    public function userResetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => ['nullable', 'string', 'min:6'],
        ]);

        $settings = SystemSetting::current();
        $newPass = $validated['new_password'] ?: ($user->isSiswa() ? $settings->default_student_password : 'password123');

        $user->update([
            'password' => Hash::make($newPass),
            'must_change_password' => true,
        ]);

        AuditLog::record('reset_password', "Mereset password pengguna {$user->username}");

        return back()->with('success', "Password untuk {$user->name} berhasil direset ke '{$newPass}' dan diwajibkan ganti saat login berikutnya.");
    }

    // ==========================================
    // 2. DATA MASTER: TAHUN AJARAN & KELAS
    // ==========================================

    public function academicYearsIndex(): View
    {
        $academicYears = AcademicYear::withCount(['classes', 'schedules'])->orderBy('name', 'desc')->get();

        return view('admin.master.academic_years', compact('academicYears'));
    }

    public function academicYearStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:ganjil,genap'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        AcademicYear::create($validated);
        AuditLog::record('create_academic_year', "Menambah tahun ajaran {$validated['name']} semester {$validated['semester']}");

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function academicYearSetActive(AcademicYear $academicYear): RedirectResponse
    {
        AcademicYear::query()->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        AuditLog::record('set_active_academic_year', "Mengaktifkan tahun ajaran {$academicYear->name} semester {$academicYear->semester}");

        return back()->with('success', "Tahun ajaran {$academicYear->name} ({$academicYear->semester}) sekarang berstatus aktif.");
    }

    public function classesIndex(): View
    {
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher', 'classAdminUser'])
            ->withCount('students')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $adminKelasUsers = User::where('role', 'admin_kelas')->orderBy('name')->get();

        return view('admin.master.classes', compact('classes', 'teachers', 'academicYears', 'adminKelasUsers'));
    }

    public function classStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'level' => ['required', 'in:10,11,12'],
            'major' => ['required', 'string', 'max:30'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'class_admin_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $class = SchoolClass::create($validated);
        AuditLog::record('create_class', "Menambahkan kelas {$class->name}");

        return back()->with('success', "Kelas {$class->name} berhasil ditambahkan.");
    }

    public function classUpdate(Request $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'level' => ['required', 'in:10,11,12'],
            'major' => ['required', 'string', 'max:30'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'class_admin_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $class->update($validated);
        AuditLog::record('update_class', "Memperbarui kelas {$class->name}");

        return back()->with('success', "Kelas {$class->name} berhasil diperbarui.");
    }

    // ==========================================
    // 3. DATA MASTER: GURU & SISWA + CSV
    // ==========================================

    public function teachersIndex(Request $request): View
    {
        $query = Teacher::query()->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.master.teachers', compact('teachers'));
    }

    public function teacherStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:30', 'unique:teachers,nip', 'unique:users,username'],
            'gender' => ['required', 'in:L,P'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::beginTransaction();
        try {
            $password = $validated['password'] ?: 'guru12345';
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['nip'],
                'email' => $validated['email'] ?? null,
                'role' => 'guru',
                'password' => Hash::make($password),
                'phone_number' => $validated['phone'] ?? null,
                'must_change_password' => true,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'] ?? null,
                'specialization' => $validated['specialization'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            DB::commit();
            AuditLog::record('create_teacher', "Menambah data guru {$validated['name']} (NIP: {$validated['nip']})");

            return back()->with('success', "Guru {$validated['name']} berhasil didaftarkan. Password default: {$password}");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan guru: '.$e->getMessage())->withInput();
        }
    }

    public function teacherImportCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ], [
            'file.required' => 'Pilih file CSV untuk diimpor.',
            'file.mimes' => 'File harus berupa format CSV (.csv).',
        ]);

        $result = CsvService::importTeachers($request->file('file'));

        $msg = "Impor selesai: {$result['success']} guru berhasil ditambahkan, {$result['skipped']} dilewati.";
        if (! empty($result['errors'])) {
            return back()->with('warning', $msg.' Catatan: '.implode(' | ', array_slice($result['errors'], 0, 3)));
        }

        return back()->with('success', $msg);
    }

    public function teacherExportCsv(): StreamedResponse
    {
        $teachers = Teacher::with('user')->orderBy('name')->get();
        $headers = ['NIP', 'Nama', 'Jenis Kelamin', 'Bidang Studi', 'No HP', 'Alamat', 'Email'];
        $rows = [];

        foreach ($teachers as $t) {
            $rows[] = [
                $t->nip,
                $t->name,
                $t->gender,
                $t->specialization,
                $t->phone,
                $t->address,
                $t->user->email ?? '',
            ];
        }

        AuditLog::record('export_teachers', 'Mengekspor data guru ke CSV');

        return CsvService::downloadCsv('data_guru_smanli_'.date('Ymd_His').'.csv', $headers, $rows);
    }

    public function studentsIndex(Request $request): View
    {
        $query = Student::query()->with(['user', 'schoolClass']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.master.students', compact('students', 'classes'));
    }

    public function studentStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis', 'unique:users,username'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
            'gender' => ['required', 'in:L,P'],
            'class_id' => ['required', 'exists:classes,id'],
            'phone_parent' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
        ]);

        $settings = SystemSetting::current();
        $defaultPassword = $settings->default_student_password ?: 'smanli2026';

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['nis'],
                'role' => 'siswa',
                'password' => Hash::make($defaultPassword),
                'must_change_password' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'class_id' => $validated['class_id'],
                'phone_parent' => $validated['phone_parent'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            DB::commit();
            AuditLog::record('create_student', "Menambah data siswa {$validated['name']} (NIS: {$validated['nis']})");

            return back()->with('success', "Siswa {$validated['name']} berhasil didaftarkan. Password awal: {$defaultPassword}");
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mendaftar siswa: '.$e->getMessage())->withInput();
        }
    }

    public function studentImportCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ], [
            'file.required' => 'Pilih file CSV siswa untuk diimpor.',
            'file.mimes' => 'File harus berekstensi CSV (.csv).',
        ]);

        $result = CsvService::importStudents($request->file('file'));

        $msg = "Impor selesai: {$result['success']} siswa berhasil ditambahkan, {$result['skipped']} dilewati.";
        if (! empty($result['errors'])) {
            return back()->with('warning', $msg.' Catatan: '.implode(' | ', array_slice($result['errors'], 0, 3)));
        }

        return back()->with('success', $msg);
    }

    public function studentExportCsv(Request $request): StreamedResponse
    {
        $query = Student::with(['schoolClass', 'user']);
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        $students = $query->orderBy('name')->get();

        $headers = ['NIS', 'NISN', 'Nama Siswa', 'Kelas', 'Jenis Kelamin', 'No HP Orang Tua', 'Alamat'];
        $rows = [];

        foreach ($students as $s) {
            $rows[] = [
                $s->nis,
                $s->nisn,
                $s->name,
                $s->schoolClass->name ?? '-',
                $s->gender,
                $s->phone_parent,
                $s->address,
            ];
        }

        AuditLog::record('export_students', 'Mengekspor data siswa ke CSV');

        return CsvService::downloadCsv('data_siswa_smanli_'.date('Ymd_His').'.csv', $headers, $rows);
    }

    // ==========================================
    // 4. MATA PELAJARAN & JADWAL
    // ==========================================

    public function subjectsIndex(): View
    {
        $subjects = Subject::withCount('schedules')->orderBy('name')->get();

        return view('admin.master.subjects', compact('subjects'));
    }

    public function subjectStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Subject::create($validated);
        AuditLog::record('create_subject', "Menambah mata pelajaran {$validated['name']} ({$validated['code']})");

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function schedulesIndex(Request $request): View
    {
        $query = Schedule::with(['academicYear', 'schoolClass', 'subject', 'teacher']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('day')) {
            $query->where('day_of_week', $request->day);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(20)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $activeYear = AcademicYear::getActive();

        return view('admin.master.schedules', compact('schedules', 'classes', 'subjects', 'teachers', 'activeYear'));
    }

    public function scheduleStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'period_start' => ['required', 'integer', 'min:1', 'max:12'],
            'period_end' => ['required', 'integer', 'gte:period_start', 'max:12'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ]);

        $schedule = Schedule::create($validated);
        AuditLog::record('create_schedule', "Menambah jadwal {$schedule->subject->name} hari {$schedule->day_of_week}");

        return back()->with('success', 'Jadwal pelajaran berhasil disimpan.');
    }

    // ==========================================
    // 5. PENGATURAN SISTEM & KOORDINAT SEKOLAH
    // ==========================================

    public function settingsIndex(): View
    {
        $settings = SystemSetting::current();

        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request): RedirectResponse
    {
        $settings = SystemSetting::current();

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_npsn' => ['required', 'string', 'max:30'],
            'school_address' => ['required', 'string'],
            'school_phone' => ['nullable', 'string', 'max:30'],
            'school_email' => ['nullable', 'email', 'max:100'],
            'headmaster_name' => ['required', 'string', 'max:255'],
            'headmaster_nip' => ['nullable', 'string', 'max:30'],
            'school_latitude' => ['required', 'numeric', 'between:-90,90'],
            'school_longitude' => ['required', 'numeric', 'between:-180,180'],
            'allowed_radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'max_gps_accuracy_meters' => ['required', 'integer', 'min:5', 'max:200'],
            'require_location' => ['boolean'],
            'fallback_on_location_error' => ['required', 'in:reject,need_verification'],
            'default_session_minutes' => ['required', 'integer', 'min:10', 'max:240'],
            'default_student_password' => ['required', 'string', 'min:6'],
        ]);

        $validated['require_location'] = $request->boolean('require_location');
        $settings->update($validated);

        AuditLog::record('update_system_settings', 'Memperbarui koordinat GPS & pengaturan sistem');

        return back()->with('success', 'Pengaturan sekolah dan validasi radius lokasi berhasil diperbarui.');
    }

    // ==========================================
    // 6. AUDIT LOGS
    // ==========================================

    public function auditLogsIndex(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        return view('admin.audit_logs', compact('logs'));
    }
}
