<?php

namespace Tests\Feature;

use App\Models\ParentSummon;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisciplineAndSummonsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_kedisiplinan_can_record_violation(): void
    {
        $adminUser = User::where('role', 'admin_kedisiplinan')->first();
        $student = Student::first();
        $type = ViolationType::first();

        $this->actingAs($adminUser);

        $response = $this->post(route('kedisiplinan.violations.store'), [
            'student_id' => $student->id,
            'violation_type_id' => $type->id,
            'incident_date' => date('Y-m-d'),
            'incident_time' => '07:30',
            'location' => 'Gerbang Depan',
            'chronology' => 'Siswa datang melewati jam 07.15 WIB.',
            'follow_up_action' => 'Pencatatan buku piket.',
            'status' => 'tercatat',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('violations', [
            'student_id' => $student->id,
            'violation_type_id' => $type->id,
            'points' => $type->points,
        ]);
    }

    public function test_admin_kedisiplinan_can_create_and_print_parent_summons(): void
    {
        $adminUser = User::where('role', 'admin_kedisiplinan')->first();
        $student = Student::first();

        $this->actingAs($adminUser);

        $letterNo = '421.3/999/SMAN5-KRW/2026';

        $response = $this->post(route('kedisiplinan.summons.store'), [
            'letter_number' => $letterNo,
            'student_id' => $student->id,
            'reason' => 'Konsultasi pembinaan kedisiplinan belajar di sekolah.',
            'meeting_date' => date('Y-m-d', strtotime('+2 days')),
            'meeting_time' => '09:00',
            'meeting_place' => 'Ruang BP/BK SMAN 5 Karawang',
            'recipient_parent_name' => 'Orang Tua / Wali '.$student->name,
            'acknowledged_by_title' => 'Wakasek Kesiswaan',
            'acknowledged_by_name' => 'Drs. H. Mulyadi, M.Pd.',
            'status' => 'dikirim',
        ]);

        $summon = ParentSummon::where('letter_number', $letterNo)->first();
        $this->assertNotNull($summon);

        $response->assertRedirect(route('kedisiplinan.summons.print', $summon->id));

        // Test the print view renders properly
        $printResponse = $this->get(route('kedisiplinan.summons.print', $summon->id));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('SURAT PANGGILAN ORANG TUA / WALI');
        $printResponse->assertSee($student->name);
        $printResponse->assertSee('Ruang BP/BK SMAN 5 Karawang');
    }
}
