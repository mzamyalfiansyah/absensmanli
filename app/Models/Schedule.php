<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'period_start',
        'period_end',
        'start_time',
        'end_time',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function conditionReports(): HasMany
    {
        return $this->hasMany(ClassConditionReport::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(AttendanceSession::class)
            ->where('date', Carbon::today()->toDateString())
            ->where('status', 'open')
            ->where('expires_at', '>', Carbon::now());
    }

    public function getDayNameIndonesianAttribute(): string
    {
        return ucfirst($this->day_of_week);
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5).' - '.substr($this->end_time, 0, 5);
    }

    public function getPeriodLabelAttribute(): string
    {
        return "Jam Ke {$this->period_start}".($this->period_start != $this->period_end ? "-{$this->period_end}" : '');
    }
}
