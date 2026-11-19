<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_npsn',
        'school_address',
        'school_phone',
        'school_email',
        'headmaster_name',
        'headmaster_nip',
        'school_latitude',
        'school_longitude',
        'allowed_radius_meters',
        'max_gps_accuracy_meters',
        'require_location',
        'fallback_on_location_error',
        'default_session_minutes',
        'default_student_password',
    ];

    protected function casts(): array
    {
        return [
            'school_latitude' => 'decimal:7',
            'school_longitude' => 'decimal:7',
            'allowed_radius_meters' => 'integer',
            'max_gps_accuracy_meters' => 'integer',
            'require_location' => 'boolean',
            'default_session_minutes' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'school_name' => 'SMAN 5 Karawang',
            'school_npsn' => '20217777',
            'school_address' => 'Jl. Jend. A. Yani No. 10, Karawang, Jawa Barat',
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
    }
}
