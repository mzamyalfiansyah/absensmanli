@extends('layouts.app')

@section('title', 'Dashboard Kurikulum')
@section('page_title', 'Dashboard Admin Kurikulum & KBM')

@section('content')
<div class="space-y-6">
    <!-- Stat Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">KBM Hari Ini</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['today_schedules_count']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Guru Hadir</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['teacher_hadir_count']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Terlambat</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['teacher_terlambat_count']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Tidak Hadir</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['teacher_tidak_hadir_count']) }}</div>
            </div>
        </div>
    </div>

    <!-- Second Stat Row: Status Tugas Guru & Laporan Pending -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-6">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-3 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Memberi Tugas</div>
                <div class="text-lg sm:text-xl font-bold text-slate-900 mt-0.5">{{ $stats['absent_with_task'] }} Guru</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-3 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-file-circle-xmark"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Tanpa Tugas</div>
                <div class="text-lg sm:text-xl font-bold text-rose-700 mt-0.5">{{ $stats['absent_without_task'] }} Guru</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-3 sm:space-x-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Laporan Menunggu</div>
                <div class="text-lg sm:text-xl font-bold text-orange-600 mt-0.5">{{ $stats['pending_class_reports'] }} Laporan</div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Pusat Kendali Kurikulum & Monitoring Guru</h2>
            <p class="text-xs text-slate-500 mt-1">Periksa kepatuhan jadwal mengajar, verifikasi laporan kondisi kelas dari Admin Kelas, dan cetak rekap kehadiran resmi.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <a href="{{ route('kurikulum.teacher_monitor') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-tv mr-1.5"></i> Monitor Guru
            </a>
            <a href="{{ route('kurikulum.class_reports.index') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-clipboard-check mr-1.5"></i> Verifikasi Laporan
            </a>
            <a href="{{ route('kurikulum.recaps.student') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition flex items-center">
                <i class="fa-solid fa-chart-column mr-1.5"></i> Rekap Absensi
            </a>
        </div>
    </div>

    <!-- Laporan Kelas Menunggu Verifikasi & Monitoring Jadwal Hari Ini -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Laporan Kelas Menunggu Verifikasi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-amber-500"></i> Laporan Kelas Menunggu Verifikasi
                </h3>
                <a href="{{ route('kurikulum.class_reports.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($pendingReports as $rep)
                    <div class="py-3 flex items-start justify-between">
                        <div>
                            <div class="text-sm font-bold text-slate-800">{{ $rep->schoolClass->name ?? '-' }} &bull; {{ $rep->schedule->subject->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                Guru: <strong>{{ $rep->teacher->name ?? '-' }}</strong> &bull; Status: <span class="font-semibold text-slate-700">{{ ucfirst($rep->teacher_status) }}</span>
                                @if($rep->teacher_arrival_time) ({{ substr($rep->teacher_arrival_time, 0, 5) }}) @endif
                            </div>
                            @if($rep->has_task)
                                <div class="text-xs text-indigo-600 mt-0.5 font-medium">
                                    <i class="fa-solid fa-check-circle mr-1"></i> Ada Tugas: {{ $rep->task_description ?? '-' }}
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-1.5 ml-3">
                            <form method="POST" action="{{ route('kurikulum.class_reports.approve', $rep->id) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition" title="Verifikasi & Rekonsiliasi">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Tidak ada laporan kelas yang menunggu verifikasi saat ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Monitoring Jadwal Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-calendar-check mr-2 text-blue-600"></i> Jadwal KBM Hari Ini ({{ ucfirst($currentDay) }})
                </h3>
                <a href="{{ route('kurikulum.teacher_monitor') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Detail &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 pb-2">
                            <th class="py-2">Waktu</th>
                            <th class="py-2">Kelas</th>
                            <th class="py-2">Guru & Pelajaran</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($todaySchedules as $sch)
                            @php
                                $att = $todayTeacherAttendances[$sch->id] ?? null;
                            @endphp
                            <tr>
                                <td class="py-2.5 font-medium text-slate-600 whitespace-nowrap">{{ $sch->time_range }}</td>
                                <td class="py-2.5 font-bold text-slate-800">{{ $sch->schoolClass->name ?? '-' }}</td>
                                <td class="py-2.5">
                                    <div class="font-medium text-slate-800">{{ $sch->teacher->name ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $sch->subject->name ?? '-' }}</div>
                                </td>
                                <td class="py-2.5">
                                    @if($att)
                                        {!! $att->status_badge !!}
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Belum Ada Sesi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Tidak ada jadwal KBM tercatat hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
