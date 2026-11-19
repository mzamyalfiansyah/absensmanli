<?php

namespace Tests\Feature;

use App\Models\ClassConditionReport;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassConditionReportAndKurikulumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_kelas_can_submit_report_and_status_is_pending(): void
    {
        $adminKelasUser = User::where('role', 'admin_kelas')->first();
        $class = SchoolClass::where('class_admin_user_id', $adminKelasUser->id)->first();
        $schedule = Schedule::where('class_id', $class->id)->first();

        $this->actingAs($adminKelasUser);

        $response = $this->post(route('admin_kelas.report.store', $schedule->id), [
            'teacher_status' => 'hadir',
            'teacher_arrival_time' => '07:15',
            'has_task' => false,
            'class_condition_notes' => 'Siswa mengikuti pelajaran dengan tertib.',
        ]);

        $response->assertRedirect(route('admin_kelas.index'));

        $this->assertDatabaseHas('class_condition_reports', [
            'schedule_id' => $schedule->id,
            'class_id' => $class->id,
            'teacher_status' => 'hadir',
            'status' => 'pending', // Must be pending
        ]);
    }

    public function test_admin_kurikulum_can_approve_report_and_reconciles_teacher_attendance(): void
    {
        $adminKurikulumUser = User::where('role', 'admin_kurikulum')->first();
        $pendingReport = ClassConditionReport::where('status', 'pending')->first();
        $this->assertNotNull($pendingReport);

        $this->actingAs($adminKurikulumUser);

        $response = $this->post(route('kurikulum.class_reports.approve', $pendingReport->id), [
            'review_notes' => 'Laporan sesuai dan terverifikasi.',
        ]);

        $response->assertRedirect();

        // Check report status is approved
        $this->assertEquals('approved', $pendingReport->fresh()->status);

        // Check TeacherAttendance was reconciled
        $this->assertDatabaseHas('teacher_attendances', [
            'schedule_id' => $pendingReport->schedule_id,
            'teacher_id' => $pendingReport->teacher_id,
            'verification_method' => 'admin_kelas_verified',
            'status' => $pendingReport->teacher_status,
        ]);
    }

    public function test_kurikulum_can_view_attendance_recaps(): void
    {
        $adminKurikulumUser = User::where('role', 'admin_kurikulum')->first();
        $this->actingAs($adminKurikulumUser);

        $studentRecap = $this->get(route('kurikulum.recaps.student'));
        $studentRecap->assertStatus(200);
        $studentRecap->assertSee('Rekapitulasi Presensi Siswa');

        $teacherRecap = $this->get(route('kurikulum.recaps.teacher'));
        $teacherRecap->assertStatus(200);
        $teacherRecap->assertSee('Rekapitulasi Kehadiran Guru');
    }
}
