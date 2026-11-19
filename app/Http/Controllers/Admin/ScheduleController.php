<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->ensureNoConflict($data);

        $data['academic_year_id'] = AcademicYear::getActive()?->id;

        Schedule::create($data);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $data = $this->validated($request);
        $this->ensureNoConflict($data, $schedule->id);

        $schedule->update($data);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'teacher_id'   => ['required', 'exists:teachers,id'],
            'subject_id'   => ['required', 'exists:subjects,id'],
            'class_id'     => ['required', 'exists:classes,id'],
            'day_of_week'  => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'period_start' => ['required', 'integer', 'min:1', 'max:12'],
            'period_end'   => ['required', 'integer', 'gte:period_start', 'max:12'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
    }

    private function ensureNoConflict(array $data, ?int $ignoreId = null): void
    {
        $overlap = fn ($q) => $q->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId));

        if (Schedule::where('teacher_id', $data['teacher_id'])->where($overlap)->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Jam ini sudah ada jadwal lain untuk guru tersebut.',
            ]);
        }

        if (Schedule::where('class_id', $data['class_id'])->where($overlap)->exists()) {
            throw ValidationException::withMessages([
                'class_id' => 'Jam ini sudah ada jadwal lain untuk kelas tersebut.',
            ]);
        }
    }
}