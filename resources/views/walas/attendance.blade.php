@extends('layouts.app')

@php
$multiClass = count($classIds) > 1;
$qs = array_filter([
'start_date' => $startDate,
'end_date' => $endDate,
'class_id' => $selectedClassId,
'student_id' => $studentId,
]);
@endphp

@section('title', 'Presensi Kelas Perwalian')
@section('page_title', $class ? 'Presensi Kelas Perwalian ' . $class->name : 'Presensi Semua Kelas')

@section('content')
<div class="space-y-6">
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">
                Rekapitulasi Presensi {{ $class ? 'Kelas ' . $class->name : 'Semua Kelas' }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                @if($class)
                Wali Kelas: {{ $homeroom->name ?? '-' }}
                @else
                Menampilkan {{ $classes->count() }} kelas
                @endif
                &bull; Pantau ketidakhadiran siswa dan persentase kehadiran.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('walas.attendance.export', $qs) }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-file-export mr-1.5 text-emerald-600"></i> Ekspor CSV
            </a>
            <a href="{{ route('walas.attendance.print', $qs) }}" target="_blank"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md shadow-slate-900/10 transition flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Rekap
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase">Hadir</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ number_format($summary['hadir']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-amber-600 uppercase">Terlambat</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-0.5">{{ number_format($summary['terlambat']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-blue-600 uppercase">Izin</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-0.5">{{ number_format($summary['izin']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-purple-600 uppercase">Sakit</div>
            <div class="text-2xl font-extrabold text-purple-700 mt-0.5">{{ number_format($summary['sakit']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-rose-600 uppercase">Alpa</div>
            <div class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ number_format($summary['alpa']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <div class="text-[11px] font-semibold text-slate-500 uppercase">% Kehadiran</div>
            <div class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $percentage }}%</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('walas.attendance') }}"
            class="grid grid-cols-1 sm:grid-cols-2 {{ $classes->count() > 1 ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            {{-- Filter kelas: hanya muncul kalau user boleh melihat lebih dari satu kelas --}}
            @if($classes->count() > 1)
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Kelas</label>
                <select name="class_id" onchange="this.form.submit()"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Siswa</label>
                <select name="student_id"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Siswa</option>
                    @foreach($students as $st)
                    <option value="{{ $st->id }}" {{ $studentId == $st->id ? 'selected' : '' }}>
                        {{ $st->name }}{{ $multiClass ? ' (' . ($classNames[$st->class_id] ?? '-') . ')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Terapkan
                </button>
                <a href="{{ route('walas.attendance') }}"
                    class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        @if($multiClass)
                        <th class="py-3 px-4">Kelas</th>
                        @endif
                        <th class="py-3 px-4">Pelajaran</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Status Lokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $at)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ $at->date->format('d/m/Y') }}</div>
                            <div class="text-[11px] text-slate-500">
                                {{ $at->scanned_at ? $at->scanned_at->format('H:i:s') . ' WIB' : '-' }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono font-medium text-slate-600">{{ $at->student->nis ?? '-' }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $at->student->name ?? '-' }}</td>
                        @if($multiClass)
                        <td class="py-3 px-4 text-slate-700">{{ $classNames[$at->student->class_id ?? 0] ?? '-' }}</td>
                        @endif
                        <td class="py-3 px-4 text-slate-700">{{ $at->schedule->subject->name ?? '-' }}</td>
                        <td class="py-3 px-4">{!! $at->status_badge !!}</td>
                        <td class="py-3 px-4">{!! $at->location_status_badge !!}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $multiClass ? 7 : 6 }}" class="py-10 text-center text-slate-400">
                            Belum ada data presensi siswa pada rentang tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection