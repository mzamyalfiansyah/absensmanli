@extends('layouts.app')

@section('title', 'Monitoring Kehadiran Guru')
@section('page_title', 'Monitoring Kehadiran Guru per Jadwal KBM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Pemantauan Presensi Guru Berdasarkan Jadwal</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pantau status hadir, keterlambatan, serta pemberian tugas bagi guru yang berhalangan hadir.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs">
                Hari: {{ ucfirst($dayOfWeek) }}, {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('kurikulum.teacher_monitor') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Kelas</label>
                <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Guru</label>
                <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Tampilkan
                </button>
                <a href="{{ route('kurikulum.teacher_monitor') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Hari Ini
                </a>
            </div>
        </form>
    </div>

    <!-- Monitoring Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Jam Ke- / Waktu</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Guru Pengajar</th>
                        <th class="py-3 px-4">Presensi Guru</th>
                        <th class="py-3 px-4">Keterangan Tugas</th>
                        <th class="py-3 px-4">Laporan Admin Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $sch)
                        @php
                            $att = $teacherAttendances[$sch->id] ?? null;
                            $cr = $classReports[$sch->id] ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $sch->period_label }}</span>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $sch->time_range }} WIB</div>
                            </td>
                            <td class="py-3 px-4 font-bold text-blue-700">{{ $sch->schoolClass->name ?? '-' }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $sch->subject->name ?? '-' }}</td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $sch->teacher->name ?? '-' }}</td>
                            <td class="py-3 px-4">
                                @if($att)
                                    {!! $att->status_badge !!}
                                    @if($att->check_in_time)
                                        <div class="text-[11px] text-slate-500 mt-0.5">Pukul {{ substr($att->check_in_time, 0, 5) }} WIB</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                        Belum Ada Data
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                @if($att && $att->status === 'tidak_hadir')
                                    @if($att->has_task)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 mb-1">
                                            <i class="fa-solid fa-check mr-1"></i> Ada Tugas
                                        </span>
                                        <div class="text-[11px] text-slate-700">{{ $att->task_description ?? '-' }}</div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">
                                            <i class="fa-solid fa-xmark mr-1"></i> Tanpa Tugas
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($cr)
                                    {!! $cr->status_badge !!}
                                    <div class="text-[11px] text-slate-600 mt-1 line-clamp-1">
                                        Guru: {{ ucfirst($cr->teacher_status) }} &bull; {{ $cr->class_condition_notes ?? 'Kondusif' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Belum lapor</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Tidak ada jadwal KBM yang tercatat pada hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
