@extends('layouts.app')

@section('title', 'Laporan Admin Kelas')
@section('page_title', 'Laporan Admin Kelas ' . $class->name)

@section('content')
<div class="space-y-6">
    <form method="GET" class="flex items-end gap-3 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <label class="block text-xs font-semibold text-slate-700">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}"
                class="mt-1 rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
        </div>
        <button
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Tampilkan</button>
    </form>

    @forelse($reports as $r)
    @php $abs = $absences[$r->schedule_id] ?? collect(); @endphp
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span
                    class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">{{ $r->schedule->period_label }}</span>
                <span class="text-xs font-mono text-slate-500">{{ $r->schedule->time_range }}</span>
                <span class="text-sm font-bold text-slate-900">{{ $r->schedule->subject->name ?? '-' }}</span>
            </div>
            {!! $r->status_badge !!}
        </div>

        <div class="text-xs text-slate-600 space-y-1">
            <div>Guru: <strong>{{ $r->teacher->name ?? '-' }}</strong> &mdash;
                <strong>{{ ucfirst(str_replace('_', ' ', $r->teacher_status)) }}</strong>
                @if($r->teacher_arrival_time) ({{ substr($r->teacher_arrival_time, 0, 5) }}) @endif
            </div>
            @if($r->has_task)
            <div class="text-indigo-600 font-semibold">Ada tugas: {{ $r->task_description }}</div>
            @endif
            @if($r->class_condition_notes)
            <div>Kondisi kelas: {{ $r->class_condition_notes }}</div>
            @endif
            <div class="text-slate-400">Dilaporkan oleh {{ $r->submittedByUser->name ?? '-' }} pukul
                {{ $r->updated_at->format('H:i') }}</div>
        </div>

        <div class="border-t border-slate-100 pt-3">
            <div class="text-xs font-semibold text-slate-700 mb-1.5">Siswa tidak hadir ({{ $abs->count() }})</div>
            @forelse($abs as $a)
            <div class="flex items-center gap-2 py-1 text-xs">
                <span class="font-semibold text-slate-800">{{ $a->student->name }}</span>
                {!! $a->status_badge !!}
                @if($a->attachment)
                <a href="{{ route('attendance.attachment', $a->id) }}" target="_blank"
                    class="text-blue-600 font-semibold">
                    <i class="fa-solid fa-paperclip"></i> Lihat surat
                </a>
                @endif
            </div>
            @empty
            <div class="text-xs text-slate-400">Semua siswa hadir.</div>
            @endforelse
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-xs text-slate-400">
        Belum ada laporan dari Admin Kelas pada tanggal ini.
    </div>
    @endforelse
</div>
@endsection