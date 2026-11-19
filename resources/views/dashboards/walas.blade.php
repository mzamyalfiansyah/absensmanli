@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')
@section('page_title', 'Dashboard Wali Kelas')

@section('content')
<div class="space-y-6">
    @if(!$homeroomClass)
    <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2 text-amber-600"></i>
        Akun Anda belum ditugaskan sebagai Wali Kelas pada kelas manapun. Silakan hubungi SuperAdmin untuk penugasan
        kelas perwalian.
    </div>
    @else
    <!-- Header Info Kelas -->
    <div
        class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-4 sm:p-6 text-white shadow-lg shadow-blue-900/10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span
                class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white uppercase tracking-wider">Kelas
                Perwalian</span>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-1.5">{{ $homeroomClass->name }} (Tingkat
                {{ $homeroomClass->level }} - {{ $homeroomClass->major }})</h2>
            <p class="text-xs text-blue-100 mt-1">
                Tahun Ajaran: {{ $homeroomClass->academicYear->name ?? '-' }} &bull; Wali Kelas:
                {{ $homeroomClass->homeroomTeacher->name ?? auth()->user()->name }}
                {{ auth()->user()->name }}
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <a href="{{ route('walas.attendance') }}"
                class="w-full sm:w-auto justify-center px-4 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-list-check mr-1.5"></i> Detail Absensi Kelas
            </a>
            <a href="{{ route('walas.attendance.print') }}" target="_blank"
                class="w-full sm:w-auto justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center border border-blue-400">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Rekap
            </a>
        </div>
    </div>

    <!-- Stat Kehadiran Hari Ini -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs">
            <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa</div>
            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total_students'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Siswa Terdaftar</div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs">
            <div class="text-[11px] sm:text-xs font-semibold text-emerald-600 uppercase tracking-wider">Hadir Hari Ini
            </div>
            <div class="text-xl sm:text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['hadir_today'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Siswa Hadir</div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs">
            <div class="text-[11px] sm:text-xs font-semibold text-purple-600 uppercase tracking-wider">Sakit</div>
            <div class="text-xl sm:text-2xl font-extrabold text-purple-700 mt-0.5">{{ $stats['sakit_today'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Dengan Surat</div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs">
            <div class="text-[11px] sm:text-xs font-semibold text-blue-600 uppercase tracking-wider">Izin</div>
            <div class="text-xl sm:text-2xl font-extrabold text-blue-700 mt-0.5">{{ $stats['izin_today'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Izin Resmi</div>
        </div>

        <div class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs col-span-2 lg:col-span-1">
            <div class="text-[11px] sm:text-xs font-semibold text-rose-600 uppercase tracking-wider">Alpa</div>
            <div class="text-xl sm:text-2xl font-extrabold text-rose-700 mt-0.5">{{ $stats['alpa_today'] }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5">Tanpa Keterangan</div>
        </div>
    </div>

    <!-- Siswa Kelas & Pelanggaran Terkini -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Daftar Siswa Perwalian -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-user-graduate mr-2 text-blue-600"></i> Rombongan Belajar
                    {{ $homeroomClass->name }}
                </h3>
                <span class="text-xs text-slate-500">{{ count($students) }} siswa</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 pb-2">
                            <th class="py-2">No</th>
                            <th class="py-2">NIS</th>
                            <th class="py-2">Nama Siswa</th>
                            <th class="py-2">L/P</th>
                            <th class="py-2 text-right">Poin Pelanggaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $s)
                        <tr>
                            <td class="py-2.5 text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-2.5 font-mono text-slate-600">{{ $s->nis }}</td>
                            <td class="py-2.5 font-bold text-slate-800">{{ $s->name }}</td>
                            <td class="py-2.5 text-slate-600">{{ $s->gender }}</td>
                            <td
                                class="py-2.5 text-right font-bold {{ $s->total_violation_points > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $s->total_violation_points }} Poin
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada siswa terdaftar di kelas
                                ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pelanggaran Terkini di Kelas Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-amber-500"></i> Catatan Disiplin Kelas
                </h3>
                <a href="{{ route('walas.violations') }}"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-700">Semua &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentViolations as $v)
                <div class="py-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-slate-800">{{ $v->student->name }}</div>
                        <span class="text-xs font-bold text-rose-600">+{{ $v->points }} Poin</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ $v->violationType->name ?? '-' }}</div>
                    <div class="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                        <span>{{ $v->incident_date->format('d/m/Y') }}</span>
                        {!! $v->status_badge !!}
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-slate-400">Alhamdulillah, belum ada catatan pelanggaran pada
                    kelas ini.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection