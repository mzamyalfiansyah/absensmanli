<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\ClassConditionReport;
use App\Models\ParentSummon;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed realistic demo data for SMAN 5 Karawang.
     */
    public function run(): void
    {
        $today = Carbon::today()->toDateString();
        $academicYear = AcademicYear::getActive() ?: AcademicYear::first();
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        // 1. Create Admins for each role
        $adminKedisiplinanUser = User::firstOrCreate([
            'username' => 'adminkedisiplinan',
        ], [
            'name' => 'Hj. Rina Marlina, S.Pd. (Admin Kedisiplinan)',
            'email' => 'kedisiplinan@sman5karawang.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin_kedisiplinan',
            'is_active' => true,
            'must_change_password' => false,
            'phone_number' => '081298765431',
        ]);

        $adminKurikulumUser = User::firstOrCreate([
            'username' => 'adminkurikulum',
        ], [
            'name' => 'Bambang Sutejo, M.Pd. (Admin Kurikulum)',
            'email' => 'kurikulum@sman5karawang.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin_kurikulum',
            'is_active' => true,
            'must_change_password' => false,
            'phone_number' => '081298765432',
        ]);

        // 2. Create Teachers
        $teachersData = [
            [
                'nip' => '197508102005011002',
                'name' => 'Drs. H. Mulyadi, M.Pd.',
                'gender' => 'L',
                'specialization' => 'Matematika Wajib',
                'phone' => '081311223344',
                'address' => 'Perum Galuh Mas Blok A No. 12, Karawang',
                'email' => 'mulyadi@sman5karawang.sch.id',
            ],
            [
                'nip' => '198203152009022004',
                'name' => 'Dra. Siti Nurhaliza, M.Pd.',
                'gender' => 'P',
                'specialization' => 'Bahasa Indonesia',
                'phone' => '081322334455',
                'address' => 'Jl. Tuparev No. 45, Karawang',
                'email' => 'siti.nurhaliza@sman5karawang.sch.id',
            ],
            [
                'nip' => '198904222015031005',
                'name' => 'Fajar Ramadhan, S.Pd.',
                'gender' => 'L',
                'specialization' => 'Fisika',
                'phone' => '081333445566',
                'address' => 'Klari, Karawang Timur',
                'email' => 'fajar.ramadhan@sman5karawang.sch.id',
            ],
            [
                'nip' => '199201102019032012',
                'name' => 'Dewi Sartika, S.Si.',
                'gender' => 'P',
                'specialization' => 'Biologi',
                'phone' => '081344556677',
                'address' => 'Telukjambe Timur, Karawang',
                'email' => 'dewi.sartika@sman5karawang.sch.id',
            ],
            [
                'nip' => '198511052010011018',
                'name' => 'Rahmat Hidayat, S.Kom.',
                'gender' => 'L',
                'specialization' => 'Informatika',
                'phone' => '081355667788',
                'address' => 'Perum Resinda Estate, Karawang Barat',
                'email' => 'rahmat.hidayat@sman5karawang.sch.id',
            ],
        ];

        $teacherModels = [];
        foreach ($teachersData as $tData) {
            $user = User::firstOrCreate([
                'username' => $tData['nip'],
            ], [
                'name' => $tData['name'],
                'email' => $tData['email'],
                'password' => Hash::make('guru12345'),
                'role' => 'guru',
                'is_active' => true,
                'must_change_password' => false,
                'phone_number' => $tData['phone'],
            ]);

            $teacherModels[$tData['nip']] = Teacher::firstOrCreate([
                'nip' => $tData['nip'],
            ], [
                'user_id' => $user->id,
                'name' => $tData['name'],
                'gender' => $tData['gender'],
                'specialization' => $tData['specialization'],
                'phone' => $tData['phone'],
                'address' => $tData['address'],
            ]);
        }

        // Dedicated AdminWalas User (Wali Kelas X-1)
        $adminWalasUser = User::firstOrCreate([
            'username' => 'adminwalas',
        ], [
            'name' => 'Dra. Siti Nurhaliza, M.Pd. (Wali Kelas X-1)',
            'email' => 'walas.x1@sman5karawang.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin_walas',
            'is_active' => true,
            'must_change_password' => false,
            'phone_number' => '081322334455',
        ]);
        // Connect teacher record to Dra Siti Nurhaliza
        $walasTeacher = $teacherModels['198203152009022004'];

        // Dedicated AdminKelas User (Ketua Kelas X-1)
        $adminKelasUser = User::firstOrCreate([
            'username' => 'adminkelas',
        ], [
            'name' => 'Arya Pratama (Admin Kelas X-1)',
            'email' => 'adminkelas.x1@sman5karawang.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin_kelas',
            'is_active' => true,
            'must_change_password' => false,
            'phone_number' => '085711223301',
        ]);

        // 3. Create Classes
        $classX1 = SchoolClass::firstOrCreate([
            'name' => 'X-1',
        ], [
            'level' => '10',
            'major' => 'Umum',
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $walasTeacher->id,
            'homeroom_user_id' => $adminWalasUser->id,
            'class_admin_user_id' => $adminKelasUser->id,
        ]);

        $classX2 = SchoolClass::firstOrCreate(['name' => 'X-2'], [
            'level' => '10',
            'major' => 'Umum',
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacherModels['197508102005011002']->id,
        ]);

        $classXI1 = SchoolClass::firstOrCreate(['name' => 'XI MIPA 1'], [
            'level' => '11',
            'major' => 'MIPA',
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacherModels['198904222015031005']->id,
        ]);

        $classXII1 = SchoolClass::firstOrCreate(['name' => 'XII MIPA 1'], [
            'level' => '12',
            'major' => 'MIPA',
            'academic_year_id' => $academicYear->id,
            'homeroom_teacher_id' => $teacherModels['199201102019032012']->id,
        ]);

        // 4. Create Students for Class X-1
        $studentsData = [
            ['nis' => '20261001', 'nisn' => '0081234501', 'name' => 'Arya Pratama', 'gender' => 'L', 'phone' => '085711223301', 'user' => $adminKelasUser],
            ['nis' => '20261002', 'nisn' => '0081234502', 'name' => 'Annisa Rahmawati', 'gender' => 'P', 'phone' => '085711223302'],
            ['nis' => '20261003', 'nisn' => '0081234503', 'name' => 'Budi Santoso', 'gender' => 'L', 'phone' => '085711223303'],
            ['nis' => '20261004', 'nisn' => '0081234504', 'name' => 'Citra Lestari', 'gender' => 'P', 'phone' => '085711223304'],
            ['nis' => '20261005', 'nisn' => '0081234505', 'name' => 'Dedi Kurniawan', 'gender' => 'L', 'phone' => '085711223305'],
            ['nis' => '20261006', 'nisn' => '0081234506', 'name' => 'Eka Putri Handayani', 'gender' => 'P', 'phone' => '085711223306'],
            ['nis' => '20261007', 'nisn' => '0081234507', 'name' => 'Fauzan Rizky Akbar', 'gender' => 'L', 'phone' => '085711223307'],
            ['nis' => '20261008', 'nisn' => '0081234508', 'name' => 'Gita Maharani', 'gender' => 'P', 'phone' => '085711223308'],
        ];

        $studentModels = [];
        foreach ($studentsData as $sData) {
            // If user already defined (like admin kelas) or create new student user
            if (isset($sData['user'])) {
                $user = $sData['user'];
            } else {
                $user = User::firstOrCreate([
                    'username' => $sData['nis'],
                ], [
                    'name' => $sData['name'],
                    'email' => $sData['nis'].'@siswa.sman5karawang.sch.id',
                    'password' => Hash::make('smanli2026'),
                    'role' => 'siswa',
                    'is_active' => true,
                    'must_change_password' => true,
                    'phone_number' => $sData['phone'],
                ]);
            }

            $studentModels[$sData['nis']] = Student::firstOrCreate([
                'nis' => $sData['nis'],
            ], [
                'user_id' => $user->id,
                'nisn' => $sData['nisn'],
                'name' => $sData['name'],
                'gender' => $sData['gender'],
                'class_id' => $classX1->id,
                'phone_parent' => '0812998877'.substr($sData['nis'], -2),
                'address' => 'Karawang Kulon, Kec. Karawang Barat',
            ]);
        }

        // 5. Create Subjects
        $subjectsData = [
            ['code' => 'MTK-WJB', 'name' => 'Matematika Wajib', 'desc' => 'Matematika Wajib Kurikulum Merdeka'],
            ['code' => 'BINDO', 'name' => 'Bahasa Indonesia', 'desc' => 'Bahasa Indonesia Tingkat Menengah'],
            ['code' => 'FIS-01', 'name' => 'Fisika', 'desc' => 'Fisika Mekanika dan Termodinamika'],
            ['code' => 'BIO-01', 'name' => 'Biologi', 'desc' => 'Biologi Sel dan Genetika'],
            ['code' => 'INF-01', 'name' => 'Informatika', 'desc' => 'Algoritma Pemrograman dan Jaringan'],
            ['code' => 'BING', 'name' => 'Bahasa Inggris', 'desc' => 'General English & Communication'],
        ];

        $subjectModels = [];
        foreach ($subjectsData as $s) {
            $subjectModels[$s['code']] = Subject::firstOrCreate(['code' => $s['code']], [
                'name' => $s['name'],
                'description' => $s['desc'],
            ]);
        }

        // 6. Create Schedules across all days so tests on ANY weekday or weekend work!
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'senin',
        ];
        $currentDayIndo = $dayMap[Carbon::now()->format('l')] ?? 'senin';

        // Ensure current day has full schedule for X-1
        $daySchedules = [
            [
                'day_of_week' => $currentDayIndo,
                'period_start' => 1,
                'period_end' => 2,
                'start_time' => '07:15:00',
                'end_time' => '08:45:00',
                'subject_id' => $subjectModels['MTK-WJB']->id,
                'teacher_id' => $teacherModels['197508102005011002']->id,
                'class_id' => $classX1->id,
            ],
            [
                'day_of_week' => $currentDayIndo,
                'period_start' => 3,
                'period_end' => 4,
                'start_time' => '08:45:00',
                'end_time' => '10:15:00',
                'subject_id' => $subjectModels['BINDO']->id,
                'teacher_id' => $teacherModels['198203152009022004']->id,
                'class_id' => $classX1->id,
            ],
            [
                'day_of_week' => $currentDayIndo,
                'period_start' => 5,
                'period_end' => 6,
                'start_time' => '10:30:00',
                'end_time' => '12:00:00',
                'subject_id' => $subjectModels['FIS-01']->id,
                'teacher_id' => $teacherModels['198904222015031005']->id,
                'class_id' => $classX1->id,
            ],
            [
                'day_of_week' => $currentDayIndo,
                'period_start' => 7,
                'period_end' => 8,
                'start_time' => '12:45:00',
                'end_time' => '14:15:00',
                'subject_id' => $subjectModels['INF-01']->id,
                'teacher_id' => $teacherModels['198511052010011018']->id,
                'class_id' => $classX1->id,
            ],
        ];

        // Also add other days
        foreach ($days as $day) {
            if ($day === $currentDayIndo) {
                continue;
            }
            $daySchedules[] = [
                'day_of_week' => $day,
                'period_start' => 1,
                'period_end' => 2,
                'start_time' => '07:15:00',
                'end_time' => '08:45:00',
                'subject_id' => $subjectModels['MTK-WJB']->id,
                'teacher_id' => $teacherModels['197508102005011002']->id,
                'class_id' => $classX1->id,
            ];
            $daySchedules[] = [
                'day_of_week' => $day,
                'period_start' => 3,
                'period_end' => 4,
                'start_time' => '08:45:00',
                'end_time' => '10:15:00',
                'subject_id' => $subjectModels['BINDO']->id,
                'teacher_id' => $teacherModels['198203152009022004']->id,
                'class_id' => $classX1->id,
            ];
        }

        $createdSchedules = [];
        foreach ($daySchedules as $sch) {
            $createdSchedules[] = Schedule::firstOrCreate([
                'academic_year_id' => $academicYear->id,
                'class_id' => $sch['class_id'],
                'subject_id' => $sch['subject_id'],
                'teacher_id' => $sch['teacher_id'],
                'day_of_week' => $sch['day_of_week'],
                'period_start' => $sch['period_start'],
            ], [
                'period_end' => $sch['period_end'],
                'start_time' => $sch['start_time'],
                'end_time' => $sch['end_time'],
            ]);
        }

        // 7. Create an Active Dynamic Attendance Session for Today's First Schedule
        $firstSchedule = Schedule::where('day_of_week', $currentDayIndo)->where('class_id', $classX1->id)->first();
        if ($firstSchedule) {
            $session = AttendanceSession::firstOrCreate([
                'schedule_id' => $firstSchedule->id,
                'date' => $today,
            ], [
                'teacher_id' => $firstSchedule->teacher_id,
                'token' => 'SMANLI-ATT-DEMOSESSION-'.$firstSchedule->id.'-'.time(),
                'title' => 'Pertemuan KBM: '.$firstSchedule->subject->name,
                'status' => 'open',
                'opened_at' => Carbon::now()->subMinutes(10),
                'expires_at' => Carbon::now()->addMinutes(35),
            ]);

            // Seed attendances for 4 students
            $sampleStudents = [$studentModels['20261001'], $studentModels['20261002'], $studentModels['20261003'], $studentModels['20261004']];
            foreach ($sampleStudents as $s) {
                StudentAttendance::firstOrCreate([
                    'schedule_id' => $firstSchedule->id,
                    'student_id' => $s->id,
                    'date' => $today,
                ], [
                    'attendance_session_id' => $session->id,
                    'status' => 'hadir',
                    'scanned_at' => Carbon::now()->subMinutes(5),
                    'latitude' => -6.3053800,
                    'longitude' => 107.3012500,
                    'accuracy' => 12.5,
                    'distance_meters' => 5.2,
                    'location_status' => 'valid',
                ]);
            }

            // Seed 1 sick student
            StudentAttendance::firstOrCreate([
                'schedule_id' => $firstSchedule->id,
                'student_id' => $studentModels['20261005']->id,
                'date' => $today,
            ], [
                'status' => 'sakit',
                'location_status' => 'manual',
                'notes' => 'Surat dokter diserahkan orang tua',
            ]);

            // Seed Teacher Attendance for today
            TeacherAttendance::firstOrCreate([
                'schedule_id' => $firstSchedule->id,
                'teacher_id' => $firstSchedule->teacher_id,
                'date' => $today,
            ], [
                'status' => 'hadir',
                'check_in_time' => '07:10:00',
                'verification_method' => 'self_check_in',
                'has_task' => false,
            ]);

            // Seed ClassConditionReport for AdminKurikulum to review
            ClassConditionReport::firstOrCreate([
                'schedule_id' => $firstSchedule->id,
                'class_id' => $classX1->id,
                'date' => $today,
            ], [
                'submitted_by_user_id' => $adminKelasUser->id,
                'period_label' => $firstSchedule->period_label,
                'teacher_id' => $firstSchedule->teacher_id,
                'teacher_status' => 'hadir',
                'teacher_arrival_time' => '07:12:00',
                'has_task' => false,
                'absent_students_notes' => 'Dedi Kurniawan sakit (ada surat)',
                'class_condition_notes' => 'Kelas berlangsung kondusif, AC dan proyektor berfungsi dengan baik.',
                'status' => 'pending',
            ]);
        }

        // 8. Seed Sample Violations and Parent Summons
        $violationType1 = ViolationType::where('code', 'PEL-04')->first();
        $violationType2 = ViolationType::where('code', 'PEL-05')->first();

        if ($violationType1 && isset($studentModels['20261007'])) {
            $v1 = Violation::firstOrCreate([
                'student_id' => $studentModels['20261007']->id,
                'violation_type_id' => $violationType1->id,
                'incident_date' => Carbon::now()->subDays(2)->toDateString(),
            ], [
                'incident_time' => '10:30:00',
                'location' => 'Warung belakang sekolah',
                'chronology' => 'Siswa ditemukan berada di luar pagar sekolah saat jam istirahat tanpa surat izin satpam/piket.',
                'follow_up_action' => 'Diberikan teguran tertulis dan pembinaan bersama Wali Kelas.',
                'recorded_by_user_id' => $adminKedisiplinanUser->id,
                'points' => $violationType1->points,
                'status' => 'dalam_pembinaan',
            ]);
        }

        if ($violationType2 && isset($studentModels['20261007'])) {
            $v2 = Violation::firstOrCreate([
                'student_id' => $studentModels['20261007']->id,
                'violation_type_id' => $violationType2->id,
                'incident_date' => Carbon::now()->subDay()->toDateString(),
            ], [
                'incident_time' => '13:00:00',
                'location' => 'Toilet siswa lantai 2',
                'chronology' => 'Siswa kedapatan membawa dan menghisap vape di dalam toilet sekolah saat jam pergantian pelajaran.',
                'follow_up_action' => 'Barang bukti diamankan, pembinaan intensif dan pemanggilan orang tua ke sekolah.',
                'recorded_by_user_id' => $adminKedisiplinanUser->id,
                'points' => $violationType2->points,
                'status' => 'panggilan_ortu',
            ]);

            // Parent Summons Letter
            ParentSummon::firstOrCreate([
                'letter_number' => '421.3/001/SMAN5-KRW/'.Carbon::now()->year,
            ], [
                'student_id' => $studentModels['20261007']->id,
                'violation_id' => $v2->id,
                'created_by_user_id' => $adminKedisiplinanUser->id,
                'reason' => 'Konsultasi dan pembinaan kedisiplinan terkait pelanggaran tata tertib sekolah (Membawa dan menghisap rokok/vape di lingkungan sekolah).',
                'meeting_date' => Carbon::now()->addDays(2)->toDateString(),
                'meeting_time' => '09:00:00',
                'meeting_place' => 'Ruang BP/BK SMAN 5 Karawang',
                'recipient_parent_name' => 'Bapak/Ibu Orang Tua/Wali Fauzan Rizky Akbar',
                'acknowledged_by_title' => 'Wakasek Bidang Kesiswaan',
                'acknowledged_by_name' => 'Drs. H. Mulyadi, M.Pd.',
                'acknowledged_by_nip' => '197508102005011002',
                'homeroom_teacher_name' => 'Dra. Siti Nurhaliza, M.Pd.',
                'status' => 'dikirim',
                'notes' => 'Mohon hadir tepat waktu dan membawa surat panggilan ini.',
            ]);
        }
    }
}