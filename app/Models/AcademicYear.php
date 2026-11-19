<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'academic_year_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
    
    /**
     * Pilihan tahun ajaran: 3 tahun ke belakang & 3 tahun ke depan
     * dari tahun ajaran aktif (urut dari yang terbaru).
     */
    public static function yearOptions(int $range = 3): array
    {
        $active = static::getActive();
        $base = $active ? (int) substr($active->name, 0, 4) : (int) now()->year;

        $options = [];
        for ($y = $base + $range; $y >= $base - $range; $y--) {
            $options[] = [
                'start'  => $y,
                'label'  => $y . '/' . ($y + 1),
                'slug'   => $y . '-' . ($y + 1),
                'status' => $y === $base ? 'aktif' : ($y < $base ? 'lalu' : 'mendatang'),
            ];
        }

        return $options;
    }
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
