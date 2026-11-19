@extends('layouts.app')

@section('title', 'Riwayat Mengajar')
@section('page_title', 'Riwayat Kehadiran Mengajar Guru')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Riwayat Presensi Mengajar</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catatan kehadiran mengajar per jadwal KBM, waktu check-in, dan rekonsiliasi kurikulum.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Kelas & Pelajaran</th>
                        <th class="py-3 px-4">Status Kehadiran</th>
                        <th class="py-3 px-4">Waktu Masuk</th>
                        <th class="py-3 px-4">Tugas (Jika Berhalangan)</th>
                        <th class="py-3 px-4">Metode Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $at)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $at->date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-indigo-700">{{ $at->schedule->schoolClass->name ?? '-' }}</div>
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
                                        <span class="text-rose-600 font-bold text-[11px]">Tanpa Tugas</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
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
                            <td colspan="6" class="py-10 text-center text-slate-400">Belum ada riwayat kehadiran mengajar Anda.</td>
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
