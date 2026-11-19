<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_teacher_can_open_dynamic_qr_session(): void
    {
        $teacher = Teacher::with('user')->first();
        $this->assertNotNull($teacher, 'A teacher must be seeded');

        // Ensure teacher user does not need password change (bypass middleware)
        $teacher->user->update(['must_change_password' => false]);

        // Get a schedule for this teacher
        $schedule = Schedule::where('teacher_id', $teacher->id)->first();
        $this->assertNotNull($schedule, 'Teacher should have at least one schedule');

        $this->actingAs($teacher->user);

        // Close any existing open sessions for this schedule to avoid unique token conflicts
        AttendanceSession::where('schedule_id', $schedule->id)
            ->where('status', 'open')
            ->update(['status' => 'closed', 'closed_at' => Carbon::now()]);

        // Open a new session via the route
        $response = $this->post(route('guru.sessions.open', $schedule->id), [
            'duration' => 60,
            'title' => 'Sesi Ujian Presensi',
        ]);

        // Should redirect to session show page (NOT to password.change)
        $response->assertRedirect();
        $this->assertStringNotContainsString(
            'password',
            $response->headers->get('Location') ?? '',
            'Should not redirect to password change page'
        );

        // Verify session was created by checking database directly
        // Use whereDate() + assertDatabaseHas for SQLite/MySQL compatibility
        $this->assertDatabaseHas('attendance_sessions', [
            'schedule_id' => $schedule->id,
            'teacher_id' => $teacher->id,
            'status' => 'open',
        ]);

        // Also verify session token is not empty
        $session = AttendanceSession::where('schedule_id', $schedule->id)
            ->where('status', 'open')
            ->latest()
            ->first();
        $this->assertNotNull($session, 'A new open session should exist after calling openSession');
        $this->assertNotEmpty($session->token);
        $this->assertEquals('open', $session->status);
    }

    public function test_student_can_scan_qr_with_valid_geolocation(): void
    {
        // Use student 20261006 who is NOT pre-seeded as attended (seeder only marks 20261001-20261005)
        $studentUser = User::where('username', '20261006')->first();
        $this->assertNotNull($studentUser, 'Student 20261006 should be seeded');
        $studentUser->update(['must_change_password' => false]);
        $student = $studentUser->student;
        $this->assertNotNull($student, 'Student record for 20261006 should exist');

        $session = AttendanceSession::where('status', 'open')->first();
        $this->assertNotNull($session, 'An open attendance session should be seeded');

        // Ensure this student has no existing attendance for this session's schedule today
        // Use whereDate() for SQLite compatibility
        StudentAttendance::where('schedule_id', $session->schedule_id)
            ->where('student_id', $student->id)
            ->whereDate('date', Carbon::today())
            ->delete();

        // Ensure student is in the correct class for the schedule
        $student->update(['class_id' => $session->schedule->class_id]);

        $this->actingAs($studentUser);

        // Scan with school coordinates (distance ~ 0m)
        $response = $this->postJson(route('siswa.scan.submit'), [
            'qr_token' => $session->token,
            'latitude' => -6.3053800,
            'longitude' => 107.3012500,
            'accuracy' => 10,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('student_attendances', [
            'student_id' => $student->id,
            'schedule_id' => $session->schedule_id,
            'location_status' => 'valid',
        ]);
    }

    public function test_student_cannot_record_duplicate_attendance(): void
    {
        // Student 20261002 was already seeded as having attended in DemoDataSeeder
        // (Note: 20261001 is admin_kelas role, NOT a pure siswa user)
        $studentUser = User::where('username', '20261002')->where('role', 'siswa')->first();
        $this->assertNotNull($studentUser, 'Student 20261002 with siswa role should be seeded');
        $studentUser->update(['must_change_password' => false]);
        $student = $studentUser->student;
        $this->assertNotNull($student, 'Student record for 20261002 should exist');

        $session = AttendanceSession::where('status', 'open')->first();
        $this->assertNotNull($session, 'An open attendance session should be seeded');

        // Ensure student is in the correct class for duplicate check to trigger
        $student->update(['class_id' => $session->schedule->class_id]);

        // Ensure an attendance record exists for this student+schedule+date (create if not)
        // Use whereDate() for SQLite compatibility before trying to create
        $existingAttendance = StudentAttendance::where('schedule_id', $session->schedule_id)
            ->where('student_id', $student->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (! $existingAttendance) {
            StudentAttendance::insert([
                'attendance_session_id' => $session->id,
                'schedule_id' => $session->schedule_id,
                'student_id' => $student->id,
                'date' => Carbon::today()->toDateString(),
                'status' => 'hadir',
                'scanned_at' => Carbon::now()->subMinutes(5),
                'latitude' => -6.3053800,
                'longitude' => 107.3012500,
                'accuracy' => 12.5,
                'distance_meters' => 5.2,
                'location_status' => 'valid',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->actingAs($studentUser);

        // Try to scan again — should be rejected as duplicate
        $response = $this->postJson(route('siswa.scan.submit'), [
            'qr_token' => $session->token,
            'latitude' => -6.3053800,
            'longitude' => 107.3012500,
            'accuracy' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_student_cannot_scan_expired_session(): void
    {
        $studentUser = User::where('username', '20261003')->where('role', 'siswa')->first();
        $this->assertNotNull($studentUser, 'Student 20261003 should be seeded');
        $studentUser->update(['must_change_password' => false]);

        $openSession = AttendanceSession::where('status', 'open')->first();
        $this->assertNotNull($openSession, 'An open session should be seeded');

        // Create a dedicated expired session using a different schedule to avoid unique constraint
        $expiredSession = AttendanceSession::create([
            'schedule_id' => $openSession->schedule_id,
            'teacher_id' => $openSession->teacher_id,
            'date' => Carbon::today()->toDateString(),
            'token' => 'EXPIRED-TEST-TOKEN-'.time(),
            'title' => 'Expired Test Session',
            'status' => 'expired',
            'opened_at' => Carbon::now()->subHour(),
            'expires_at' => Carbon::now()->subMinutes(10),
            'closed_at' => Carbon::now()->subMinutes(10),
        ]);

        $this->actingAs($studentUser);

        $response = $this->postJson(route('siswa.scan.submit'), [
            'qr_token' => $expiredSession->token,
            'latitude' => -6.3053800,
            'longitude' => 107.3012500,
            'accuracy' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_teacher_can_self_check_in(): void
    {
        $teacher = Teacher::with('user')->first();
        $this->assertNotNull($teacher, 'A teacher must be seeded');

        $schedule = Schedule::where('teacher_id', $teacher->id)->first();
        $this->assertNotNull($schedule, 'Teacher should have at least one schedule');

        $this->actingAs($teacher->user);

        // Delete existing teacher attendance if seeded, so we can create a fresh one
        TeacherAttendance::where('schedule_id', $schedule->id)
            ->where('teacher_id', $teacher->id)
            ->where('date', Carbon::today()->toDateString())
            ->delete();

        $response = $this->post(route('guru.checkin', $schedule->id), [
            'status' => 'hadir',
            'check_in_time' => '07:15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teacher_attendances', [
            'teacher_id' => $teacher->id,
            'schedule_id' => $schedule->id,
            'status' => 'hadir',
        ]);
    }
}
