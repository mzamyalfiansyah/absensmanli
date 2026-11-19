<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    /**
     * Daftar semua tahun ajaran (3 tahun ke belakang & ke depan).
     */
    public function index(): View
    {
        $options = AcademicYear::yearOptions();

        return view('tahun_ajaran.index', compact('options'));
    }

    /**
     * Ringkasan satu tahun ajaran. Slug berformat 2025-2026.
     */
    public function show(string $tahun): View
    {
        [$start, $end] = array_map('intval', explode('-', $tahun));

        // Slug harus berurutan, mis. 2025-2026 (bukan 2025-2030)
        abort_unless($end === $start + 1, 404);

        $name = $start . '/' . $end;
        $year = AcademicYear::where('name', $name)->first();

        $stats = null;

        if ($year) {
            $classIds = SchoolClass::where('academic_year_id', $year->id)->pluck('id');
            $scheduleIds = Schedule::where('academic_year_id', $year->id)->pluck('id');

            $attendance = StudentAttendance::whereIn('schedule_id', $scheduleIds)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $stats = [
                'classes'   => $classIds->count(),
                'students'  => Student::whereIn('class_id', $classIds)->count(),
                'schedules' => $scheduleIds->count(),
                'attendance' => [
                    'hadir'     => (int) ($attendance['hadir'] ?? 0),
                    'terlambat' => (int) ($attendance['terlambat'] ?? 0),
                    'izin'      => (int) ($attendance['izin'] ?? 0),
                    'sakit'     => (int) ($attendance['sakit'] ?? 0),
                    'alpa'      => (int) ($attendance['alpa'] ?? 0),
                ],
            ];
        }

        $activeYear = AcademicYear::getActive();
        $isActive = $year?->is_active ?? false;

        return view('tahun_ajaran.show', [
            'name'     => $name,
            'year'     => $year,
            'stats'    => $stats,
            'isActive' => $isActive,
            'activeYear' => $activeYear,
        ]);
    }
}