@extends('layouts.app')

@section('title', 'Rekap Presensi Guru')
@section('page_title', 'Rekapitulasi Kehadiran Guru Mengajar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Rekapitulasi Kehadiran Guru Pengajar</h2>
            <p class="text-xs text-slate-500 mt-0.5">Analisis kehadiran mengajar guru, keterlambatan, dan kepatuhan pemberian tugas jika absen.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('kurikulum.recaps.teacher.export', ['start_date' => $startDate, 'end_date' => $endDate, 'teacher_id' => $teacherId]) }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-file-export mr-1.5 text-emerald-600"></i> Ekspor CSV
            </a>
            <a href="{{ route('kurikulum.recaps.print', ['type' => 'teacher', 'start_date' => $startDate, 'end_date' => $endDate, 'teacher_id' => $teacherId]) }}" target="_blank"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md shadow-slate-900/10 transition flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Dokumen Rekap
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-emerald-600 uppercase">Hadir</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ number_format($summary['hadir']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-amber-600 uppercase">Terlambat</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-0.5">{{ number_format($summary['terlambat']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-rose-600 uppercase">Tidak Hadir</div>
            <div class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ number_format($summary['tidak_hadir']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-indigo-600 uppercase">Absen Beri Tugas</div>
            <div class="text-2xl font-extrabold text-indigo-700 mt-0.5">{{ number_format($summary['tidak_hadir_dengan_tugas']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <div class="text-[11px] font-semibold text-rose-600 uppercase">Absen Tanpa Tugas</div>
            <div class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ number_format($summary['tidak_hadir_tanpa_tugas']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('kurikulum.recaps.teacher') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
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

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Guru Pengajar</label>
                <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Terapkan
                </button>
                <a href="{{ route('kurikulum.recaps.teacher') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
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
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Guru Pengajar</th>
                        <th class="py-3 px-4">Kelas & Pelajaran</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Waktu Masuk</th>
                        <th class="py-3 px-4">Tugas / Keterangan</th>
                        <th class="py-3 px-4">Metode Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $at)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $at->date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $at->teacher->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $at->teacher->nip ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-blue-700">{{ $at->schedule->schoolClass->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $at->schedule->subject->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">{!! $at->status_badge !!}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $at->check_in_time ? substr($at->check_in_time, 0, 5) . ' WIB' : '-' }}</td>
                            <td class="py-3 px-4 max-w-xs">
                                @if($at->status === 'tidak_hadir')
                                    @if($at->has_task)
                                        <div class="text-indigo-600 font-semibold mb-0.5"><i class="fa-solid fa-check mr-1"></i> Ada Tugas:</div>
                                        <div class="text-slate-600 text-[11px] line-clamp-2">{{ $at->task_description ?? '-' }}</div>
                                    @else
                                        <span class="text-rose-600 font-bold text-[11px]"><i class="fa-solid fa-xmark mr-1"></i> Tanpa Tugas</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                    {{ match($at->verification_method) {
                                        'self_check_in' => 'Verifikasi Mandiri',
                                        'qr_session' => 'Sesi QR',
                                        'admin_kelas_verified' => 'Admin Kelas',
                                        default => ucfirst($at->verification_method)
                                    } }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Belum ada data presensi guru pada rentang ini.</td>
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
