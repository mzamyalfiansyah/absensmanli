<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'must_change_password',
        'phone_number',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function administeredClass(): HasOne
    {
        return $this->hasOne(SchoolClass::class, 'class_admin_user_id');
    }

    public function classConditionReports(): HasMany
    {
        return $this->hasMany(ClassConditionReport::class, 'submitted_by_user_id');
    }

    public function recordedViolations(): HasMany
    {
        return $this->hasMany(Violation::class, 'recorded_by_user_id');
    }

    public function parentSummons(): HasMany
    {
        return $this->hasMany(ParentSummon::class, 'created_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'superadmin'], true);
    }

    public function isAdminKedisiplinan(): bool
    {
        return $this->role === 'admin_kedisiplinan' || $this->isSuperAdmin();
    }

    public function isAdminKurikulum(): bool
    {
        return $this->role === 'admin_kurikulum' || $this->isSuperAdmin();
    }

    public function isAdminWalas(): bool
    {
        return $this->role === 'admin_walas' || $this->isSuperAdmin();
    }

    public function isAdminKelas(): bool
    {
        return $this->role === 'admin_kelas';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Administrator',
            'admin_kedisiplinan' => 'Admin Kedisiplinan',
            'admin_kurikulum' => 'Admin Kurikulum',
            'admin_walas' => 'Wali Kelas',
            'admin_kelas' => 'Admin Kelas',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            default => ucfirst($this->role),
        };
    }
}