<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'violation_type_id',
        'incident_date',
        'incident_time',
        'location',
        'chronology',
        'follow_up_action',
        'recorded_by_user_id',
        'points',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'points' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function parentSummons(): HasMany
    {
        return $this->hasMany(ParentSummon::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'tercatat' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">Tercatat</span>',
            'dalam_pembinaan' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Dalam Pembinaan</span>',
            'selesai' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Selesai</span>',
            'panggilan_ortu' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">Panggilan Orang Tua</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">'.ucfirst($this->status).'</span>',
        };
    }
}
