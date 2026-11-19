<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\ViolationType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionInitialSeeder extends Seeder
{
    /**
     * Seed production baseline data.
     */
    public function run(): void
    {
        // 1. System Settings SMAN 5 Karawang
        SystemSetting::firstOrCreate([], [
            'school_name' => 'SMAN 5 Karawang',
            'school_npsn' => '20217777',
            'school_address' => 'Jl. Jend. A. Yani No. 10, Karawang Kulon, Karawang, Jawa Barat',
            'school_phone' => '(0267) 402345',
            'school_email' => 'info@sman5karawang.sch.id',
            'headmaster_name' => 'H. Suhaerudin, M.Pd.',
            'headmaster_nip' => '19680512 199403 1 004',
            'school_latitude' => -6.3053800,
            'school_longitude' => 107.3012500,
            'allowed_radius_meters' => 100,
            'max_gps_accuracy_meters' => 50,
            'require_location' => true,
            'fallback_on_location_error' => 'need_verification',
            'default_session_minutes' => 45,
            'default_student_password' => 'smanli2026',
        ]);

        // 2. Active Academic Year
        AcademicYear::firstOrCreate([
            'name' => '2025/2026',
            'semester' => 'ganjil',
        ], [
            'is_active' => true,
            'start_date' => Carbon::now()->startOfYear()->toDateString(),
            'end_date' => Carbon::now()->endOfYear()->toDateString(),
        ]);

        // 3. Violation Types
        $types = [
            [
                'code' => 'PEL-01',
                'name' => 'Terlambat hadir di sekolah lebih dari 15 menit',
                'category' => 'ringan',
                'points' => 5,
                'default_action' => 'Pencatatan piket dan pembinaan oleh tim kedisiplinan',
            ],
            [
                'code' => 'PEL-02',
                'name' => 'Atribut seragam tidak lengkap (tidak bertopi/dasi/sabuk)',
                'category' => 'ringan',
                'points' => 5,
                'default_action' => 'Teguran lisan dan kelengkapan atribut seragam',
            ],
            [
                'code' => 'PEL-03',
                'name' => 'Menggunakan gawai pribadi saat KBM tanpa instruksi guru',
                'category' => 'ringan',
                'points' => 10,
                'default_action' => 'Gawai disimpan di meja piket hingga jam sekolah usai',
            ],
            [
                'code' => 'PEL-04',
                'name' => 'Meninggalkan kelas atau lingkungan sekolah tanpa izin (membolos)',
                'category' => 'sedang',
                'points' => 20,
                'default_action' => 'Peringatan tertulis dan pembinaan bersama Wali Kelas',
            ],
            [
                'code' => 'PEL-05',
                'name' => 'Merokok atau membawa rokok/vape di lingkungan sekolah',
                'category' => 'berat',
                'points' => 35,
                'default_action' => 'Panggilan orang tua dan perjanjian bermaterai',
            ],
            [
                'code' => 'PEL-06',
                'name' => 'Terlibat perkelahian atau tawuran fisik antar pelajar',
                'category' => 'berat',
                'points' => 50,
                'default_action' => 'Surat Panggilan Orang Tua ke-1 dan skorsing sementara',
            ],
        ];

        foreach ($types as $type) {
            ViolationType::firstOrCreate(['code' => $type['code']], $type);
        }

        // 4. Default SuperAdmin
        User::firstOrCreate([
            'username' => 'superadmin',
        ], [
            'name' => 'Super Administrator SMANLI',
            'email' => 'admin@sman5karawang.sch.id',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin',
            'is_active' => true,
            'must_change_password' => false,
            'phone_number' => '081234567890',
        ]);
    }
}
