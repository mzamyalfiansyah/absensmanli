@extends('layouts.app')

@section('title', 'Kehadiran Kelas')
@section('page_title', 'Kehadiran ' . $schedule->schoolClass->name . ' - ' . ($schedule->subject->name ?? ''))

@section('content')
<div class="space-y-6">
    <form method="GET" class="flex items-end gap-3 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <label class="block text-xs font-semibold text-slate-700">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}"
                class="mt-1 rounded-xl border border-slate-300 px-3 py-2 text-xs">
        </div>
        <button
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">Tampilkan</button>
        <a href="{{ route('guru.schedules') }}"
            class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Kembali</a>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        @foreach([
        ['Hadir', $summary['hadir'], 'text-emerald-700'],
        ['Izin', $summary['izin'], 'text-blue-700'],
        ['Sakit', $summary['sakit'], 'text-purple-700'],
        ['Tanpa Alasan', $summary['alpa'], 'text-rose-700'],
        ['Belum Absen', $summary['belum'], 'text-slate-600'],
        ] as [$label, $val, $cls])
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-slate-500 uppercase">{{ $label }}</div>
            <div class="text-2xl font-extrabold {{ $cls }} mt-0.5">{{ $val }}</div>
        </div>
        @endforeach
    </div>

    @if($report)
    <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl text-xs text-indigo-900">
        Laporan Admin Kelas: kehadiran Anda dicatat
        <strong>{{ ucfirst(str_replace('_', ' ', $report->teacher_status)) }}</strong>.
        @if($report->class_condition_notes) Catatan: {{ $report->class_condition_notes }} @endif
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[560px]">
            <thead>
                <tr class="text-slate-400 border-b border-slate-100">
                    <th class="py-2">No</th>
                    <th class="py-2">NIS</th>
                    <th class="py-2">Nama</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Keterangan</th>
                    <th class="py-2">Surat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($students as $i => $s)
                @php $a = $attendances[$s->id] ?? null; @endphp
                <tr>
                    <td class="py-2.5 text-slate-400">{{ $i + 1 }}</td>
                    <td class="py-2.5 font-mono text-slate-600">{{ $s->nis }}</td>
                    <td class="py-2.5 font-bold text-slate-800">{{ $s->name }}</td>
                    <td class="py-2.5">
                        @if($a) {!! $a->status_badge !!}
                        @else <span class="text-slate-400">Belum absen</span> @endif
                    </td>
                    <td class="py-2.5 text-slate-500">{{ $a?->notes ?? '-' }}</td>
                    <td class="py-2.5">
                        @if($a?->attachment)
                        <a href="{{ route('attendance.attachment', $a->id) }}" target="_blank"
                            class="text-blue-600 font-semibold">
                            <i class="fa-solid fa-paperclip"></i> Lihat
                        </a>
                        @else - @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection