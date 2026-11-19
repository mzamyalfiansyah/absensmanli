<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'level',
        'major',
        'academic_year_id',
        'homeroom_teacher_id',
        'homeroom_user_id',
        'class_admin_user_id',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function homeroomUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_user_id');
    }

    /**
     * Kelas perwalian milik user: dikenali lewat akun wali kelas
     * (homeroom_user_id) ATAU lewat data guru (homeroom_teacher_id).
     */
    public function scopeHomeroomOf(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('homeroom_user_id', $user->id);

            if ($user->teacher) {
                $q->orWhere('homeroom_teacher_id', $user->teacher->id);
            }
        });
    }

    public function classAdminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_admin_user_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function conditionReports(): HasMany
    {
        return $this->hasMany(ClassConditionReport::class, 'class_id');
    }
}