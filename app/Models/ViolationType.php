<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'points',
        'default_action',
    ];

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function getCategoryBadgeAttribute(): string
    {
        return match ($this->category) {
            'ringan' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Ringan</span>',
            'sedang' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">Sedang</span>',
            'berat' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200">Berat</span>',
            default => ucfirst($this->category),
        };
    }
}
