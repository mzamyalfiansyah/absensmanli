@extends('layouts.app')

@section('title', 'Rekap Presensi Bulanan')
@section('page_title', 'Rekapitulasi Presensi Bulanan')

@section('content')
@php
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
@endphp

<div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    <i class="fa-solid fa-calendar-days mr-1.5"></i> {{ $monthLabel }}
                </span>
                @if($classId && $classes->firstWhere('id', $classId))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-chalkboard-user mr-1.5"></i> {{ $classes->firstWhere('id', $classId)->name }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        Semua Kelas
                    </span>
                @endif
            </div>
            <h2 class="text-base font-bold text-slate-900 mt-2">Rekapitulasi Presensi Siswa Bulanan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Rangkuman data kehadiran siswa per bulan dan tahun ajaran.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('kurikulum.recaps.chart', ['year' => $year, 'class_id' => $classId]) }}"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/10 transition flex items-center">
                <i class="fa-solid fa-chart-pie mr-1.5"></i> Lihat Grafik Absensi
            </a>
            <a href="{{ route('kurikulum.recaps.student', ['start_date' => $startDate, 'end_date' => $endDate, 'class_id' => $classId]) }}"
                class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-list mr-1.5"></i> Log Detail Harian
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase">Hadir</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ number_format($summary['hadir']) }}</div>
            <span class="text-[10px] text-slate-400">Total sesi hadir</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-amber-600 uppercase">Terlambat</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-0.5">{{ number_format($summary['terlambat']) }}</div>
            <span class="text-[10px] text-slate-400">Total terlambat</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-blue-600 uppercase">Izin</div>
            <div class="text-2xl font-extrabold text-blue-700 mt-0.5">{{ number_format($summary['izin']) }}</div>
            <span class="text-[10px] text-slate-400">Surat izin sah</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-purple-600 uppercase">Sakit</div>
            <div class="text-2xl font-extrabold text-purple-700 mt-0.5">{{ number_format($summary['sakit']) }}</div>
            <span class="text-[10px] text-slate-400">Surat dokter/wali</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-rose-600 uppercase">Alpa</div>
            <div class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ number_format($summary['alpa']) }}</div>
            <span class="text-[10px] text-slate-400">Tanpa keterangan</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <div class="text-[11px] font-semibold text-indigo-600 uppercase">% Kehadiran</div>
            <div class="text-2xl font-extrabold text-indigo-700 mt-0.5">{{ $percentage }}%</div>
            <span class="text-[10px] text-slate-400">Rasio disiplin</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('kurikulum.recaps.monthly') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Bulan</label>
                <select name="month" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    @foreach($monthNames as $num => $name)
                        <option value="{{ $num }}" {{ (int)$month === $num ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Tahun</label>
                <select name="year" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ (int)$year === $yr ? 'selected' : '' }}>
                            {{ $yr }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Kelas</label>
                <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition flex items-center justify-center">
                    <i class="fa-solid fa-filter mr-1.5"></i> Terapkan
                </button>
                <a href="{{ route('kurikulum.recaps.monthly') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Kehadiran Siswa</h3>
                <p class="text-xs text-slate-400">Total {{ $recap->count() }} siswa terdata pada periode ini</p>
            </div>
            <div class="text-xs text-slate-500">
                Periode: <strong class="text-slate-800">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> s/d <strong class="text-slate-800">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Identitas Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-3 text-center text-emerald-700">Hadir</th>
                        <th class="py-3 px-3 text-center text-amber-700">Terlambat</th>
                        <th class="py-3 px-3 text-center text-blue-700">Izin</th>
                        <th class="py-3 px-3 text-center text-purple-700">Sakit</th>
                        <th class="py-3 px-3 text-center text-rose-700">Alpa</th>
                        <th class="py-3 px-3 text-center font-extrabold text-slate-800">Total Sesi</th>
                        <th class="py-3 px-4 text-center font-extrabold text-indigo-700">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recap as $index => $item)
                        @php
                            $rate = $item['total'] > 0
                                ? round((($item['hadir'] + $item['terlambat']) / $item['total']) * 100, 1)
                                : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-center text-slate-400 font-medium">{{ $index + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $item['student']->name }}</div>
                                <div class="text-[11px] text-slate-400">NIS: {{ $item['student']->nis ?? '-' }} | NISN: {{ $item['student']->nisn ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $item['student']->schoolClass->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] py-1 px-1.5 rounded-lg text-xs font-bold {{ $item['hadir'] > 0 ? 'bg-emerald-50 text-emerald-700 font-extrabold' : 'text-slate-300' }}">
                                    {{ $item['hadir'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] py-1 px-1.5 rounded-lg text-xs font-bold {{ $item['terlambat'] > 0 ? 'bg-amber-50 text-amber-700 font-extrabold' : 'text-slate-300' }}">
                                    {{ $item['terlambat'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] py-1 px-1.5 rounded-lg text-xs font-bold {{ $item['izin'] > 0 ? 'bg-blue-50 text-blue-700 font-extrabold' : 'text-slate-300' }}">
                                    {{ $item['izin'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] py-1 px-1.5 rounded-lg text-xs font-bold {{ $item['sakit'] > 0 ? 'bg-purple-50 text-purple-700 font-extrabold' : 'text-slate-300' }}">
                                    {{ $item['sakit'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] py-1 px-1.5 rounded-lg text-xs font-bold {{ $item['alpa'] > 0 ? 'bg-rose-50 text-rose-700 font-extrabold' : 'text-slate-300' }}">
                                    {{ $item['alpa'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-slate-800">
                                {{ $item['total'] }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $rate >= 80 ? 'bg-emerald-100 text-emerald-800' : ($rate >= 60 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $rate }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                Tidak ada data absensi untuk filter bulan dan kelas yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($recap->isNotEmpty())
                    <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200 text-slate-700">
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-right">TOTAL KESELURUHAN :</td>
                            <td class="py-3 px-3 text-center text-emerald-700 font-extrabold">{{ number_format($summary['hadir']) }}</td>
                            <td class="py-3 px-3 text-center text-amber-700 font-extrabold">{{ number_format($summary['terlambat']) }}</td>
                            <td class="py-3 px-3 text-center text-blue-700 font-extrabold">{{ number_format($summary['izin']) }}</td>
                            <td class="py-3 px-3 text-center text-purple-700 font-extrabold">{{ number_format($summary['sakit']) }}</td>
                            <td class="py-3 px-3 text-center text-rose-700 font-extrabold">{{ number_format($summary['alpa']) }}</td>
                            <td class="py-3 px-3 text-center font-extrabold text-slate-900">{{ number_format($summary['total']) }}</td>
                            <td class="py-3 px-4 text-center text-indigo-700 font-extrabold">{{ $percentage }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
