<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'teacher_id',
        'date',
        'token',
        'title',
        'status',
        'opened_at',
        'expires_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'opened_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if (Carbon::now()->isAfter($this->expires_at)) {
            $this->update(['status' => 'expired']);

            return false;
        }

        return true;
    }

    public static function createNewSession(Schedule $schedule, Teacher $teacher, int $durationMinutes = 45, ?string $title = null): self
    {
        // Close any currently active session for this schedule today
        static::where('schedule_id', $schedule->id)
            ->where('date', Carbon::today()->toDateString())
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_at' => Carbon::now(),
            ]);

        $now = Carbon::now();
        $token = 'SMANLI-ATT-'.Str::upper(Str::random(12)).'-'.$schedule->id.'-'.$now->timestamp;

        return static::create([
            'schedule_id' => $schedule->id,
            'teacher_id' => $teacher->id,
            'date' => $now->toDateString(),
            'token' => $token,
            'title' => $title ?: ('Absensi '.($schedule->subject->name ?? 'Mata Pelajaran')),
            'status' => 'open',
            'opened_at' => $now,
            'expires_at' => $now->copy()->addMinutes($durationMinutes),
        ]);
    }
}
