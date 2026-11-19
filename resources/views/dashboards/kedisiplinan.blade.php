@extends('layouts.app')

@section('title', 'Dashboard Kedisiplinan')
@section('page_title', 'Dashboard Admin Kedisiplinan & Kesiswaan')

@section('content')
<div class="space-y-6">
    <!-- Stat Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Total Kasus</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['total_violations']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Hari Ini</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['today_violations']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Pembinaan</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['pending_followups']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Panggilan Ortu</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['active_summons']) }}</div>
            </div>
        </div>
    </div>

    <!-- Banner & Action Shortcuts -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Manajemen Kedisiplinan & Pemanggilan Orang Tua</h2>
            <p class="text-xs text-slate-500 mt-1">Kelola kronologi kejadian, tindak lanjut, surat panggilan berkop resmi sekolah, serta rekap poin kedisiplinan siswa.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <a href="{{ route('kedisiplinan.violations.index') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-plus mr-1.5"></i> Catat Pelanggaran
            </a>
            <a href="{{ route('kedisiplinan.summons.create') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-file-signature mr-1.5"></i> Terbitkan Panggilan
            </a>
            <a href="{{ route('kedisiplinan.violations.export') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition flex items-center">
                <i class="fa-solid fa-download mr-1.5"></i> Ekspor CSV
            </a>
        </div>
    </div>

    <!-- Grid Data: Top Poin Siswa & Riwayat Pelanggaran -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Siswa Akumulasi Poin Tertinggi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center">
                <i class="fa-solid fa-ranking-star mr-2 text-rose-600"></i> Akumulasi Poin Terbanyak
            </h3>
            <div class="divide-y divide-slate-100">
                @forelse($topStudentsWithViolations as $st)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-bold text-slate-800">{{ $st->name }}</div>
                            <div class="text-xs text-slate-500">Kelas: {{ $st->schoolClass->name ?? '-' }} &bull; NIS: {{ $st->nis }}</div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-800">
                            {{ $st->points_sum }} Poin
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">Belum ada siswa dengan catatan pelanggaran.</div>
                @endforelse
            </div>
        </div>

        <!-- Riwayat Pelanggaran Terbaru -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2 text-blue-600"></i> Riwayat Pelanggaran Terkini
                </h3>
                <a href="{{ route('kedisiplinan.violations.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 pb-2">
                            <th class="py-2">Tanggal</th>
                            <th class="py-2">Siswa</th>
                            <th class="py-2">Pelanggaran</th>
                            <th class="py-2">Poin</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentViolations as $v)
                            <tr>
                                <td class="py-3 font-medium text-slate-600">{{ $v->incident_date->format('d/m/Y') }}</td>
                                <td class="py-3">
                                    <div class="font-bold text-slate-800">{{ $v->student->name ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $v->student->schoolClass->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 text-slate-700">{{ $v->violationType->name ?? '-' }}</td>
                                <td class="py-3 font-bold text-rose-600">+{{ $v->points }}</td>
                                <td class="py-3">{!! $v->status_badge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada data pelanggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
