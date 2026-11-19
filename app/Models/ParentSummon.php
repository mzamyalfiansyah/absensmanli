<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentSummon extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_number',
        'student_id',
        'violation_id',
        'created_by_user_id',
        'reason',
        'meeting_date',
        'meeting_time',
        'meeting_place',
        'recipient_parent_name',
        'acknowledged_by_title',
        'acknowledged_by_name',
        'acknowledged_by_nip',
        'homeroom_teacher_name',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public static function generateLetterNumber(): string
    {
        $year = Carbon::now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        $sequence = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "421.3/{$sequence}/SMAN5-KRW/{$year}";
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">Draft</span>',
            'dikirim' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">Dikirim</span>',
            'hadir' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Orang Tua Hadir</span>',
            'tidak_hadir' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">Tidak Hadir</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">'.ucfirst($this->status).'</span>',
        };
    }
}
