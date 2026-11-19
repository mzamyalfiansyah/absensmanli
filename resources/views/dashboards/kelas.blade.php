@extends('layouts.app')

@section('title', 'Dashboard Admin Kelas')
@section('page_title', 'Dashboard Admin Kelas (Ketua / Sekretaris Kelas)')

@section('content')
<div class="space-y-5">
    @if(!$schoolClass)
        <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-amber-500 mt-0.5 text-lg shrink-0"></i>
            <div>
                <div class="font-bold">Kelas Belum Dikaitkan</div>
                <div class="text-xs mt-1">Akun Anda belum ditautkan ke kelas manapun sebagai Admin Kelas. Hubungi SuperAdmin sekolah.</div>
            </div>
        </div>
    @else
        {{-- Header Info Kelas --}}
        <div class="bg-gradient-to-r from-indigo-700 to-blue-800 rounded-2xl p-5 sm:p-6 text-white shadow-lg shadow-indigo-900/10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-white/20 text-white uppercase tracking-wider">Tugas Admin Kelas</span>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-2">Kelas {{ $schoolClass->name }}</h2>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-indigo-100">
                        <span><i class="fa-solid fa-chalkboard-user mr-1 opacity-75"></i>Wali Kelas: <strong>{{ $schoolClass->homeroomTeacher?->name ?? '-' }}</strong></span>
                        <span><i class="fa-solid fa-calendar-day mr-1 opacity-75"></i>Hari ini: <strong>{{ ucfirst($currentDay) }}</strong></span>
                    </div>
                </div>
                <div class="p-3 bg-white/10 rounded-xl text-xs backdrop-blur-xs sm:max-w-xs flex-shrink-0">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-info text-amber-300 mt-0.5 shrink-0"></i>
                        <span>Setiap laporan kehadiran guru berstatus <em>Pending</em> hingga diverifikasi oleh Admin Kurikulum.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Jadwal Pelajaran Hari Ini --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-day text-indigo-600"></i>
                            Jadwal Pelajaran Hari Ini ({{ ucfirst($currentDay) }})
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Catat kehadiran guru, jam kedatangan, tugas, dan kondisi kelas di setiap jam pelajaran.</p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($schedules as $sch)
                    @php $rep = $reportsToday[$sch->id] ?? null; @endphp
                    <div class="p-5 sm:p-6 hover:bg-slate-50/50 transition">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            {{-- Info Jadwal --}}
                            <div class="flex-1 min-w-0 space-y-2">
                                {{-- Jam & Mapel --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
                                        {{ $sch->period_label }}
                                    </span>
                                    <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg">
                                        {{ $sch->time_range }} WIB
                                    </span>
                                    <span class="text-sm font-bold text-slate-900">{{ $sch->subject?->name ?? '-' }}</span>
                                </div>

                                {{-- Info Guru --}}
                                <div class="text-xs text-slate-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-tie text-slate-400 w-4 text-center"></i>
                                    <span>Guru Pengajar: <strong>{{ $sch->teacher?->name ?? '-' }}</strong></span>
                                    @if($sch->teacher?->nip)
                                        <span class="text-slate-400">(NIP: {{ $sch->teacher->nip }})</span>
                                    @endif
                                </div>

                                {{-- Status Laporan --}}
                                @if($rep)
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="text-xs text-slate-500 font-medium">Laporan Anda:</span>
                                        {!! $rep->status_badge !!}
                                        <span class="text-xs text-slate-700">
                                            Guru: <strong>{{ ucfirst($rep->teacher_status) }}</strong>
                                        </span>
                                        @if($rep->teacher_arrival_time)
                                            <span class="text-xs text-slate-500">({{ substr($rep->teacher_arrival_time, 0, 5) }} WIB)</span>
                                        @endif
                                        @if($rep->has_task)
                                            <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100">
                                                <i class="fa-solid fa-file-check mr-1"></i>Ada Tugas
                                            </span>
                                        @endif
                                    </div>

                                    @if($rep->status === 'rejected' && $rep->review_notes)
                                        <div class="flex items-start gap-2 mt-1 p-2 bg-rose-50 rounded-lg border border-rose-200 text-xs text-rose-700">
                                            <i class="fa-solid fa-circle-xmark shrink-0 mt-0.5"></i>
                                            <span><strong>Ditolak:</strong> {{ $rep->review_notes }}</span>
                                        </div>
                                    @elseif($rep->status === 'approved')
                                        <div class="flex items-center gap-1.5 mt-1 text-xs text-emerald-700">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>Laporan telah diverifikasi oleh Admin Kurikulum.</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-clock mr-1"></i>Belum Dilaporkan
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="flex flex-row sm:flex-row lg:flex-col xl:flex-row gap-2 lg:items-start xl:items-center flex-shrink-0">
                                <a href="{{ route('admin_kelas.report.create', $sch->id) }}"
                                    class="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs whitespace-nowrap
                                    {{ $rep ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/20' }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>{{ $rep ? 'Edit Laporan' : 'Input Laporan Guru' }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-14 text-center">
                        <div class="text-slate-300 text-4xl mb-3"><i class="fa-solid fa-calendar-xmark"></i></div>
                        <div class="text-sm font-semibold text-slate-500">Tidak ada jadwal KBM kelas hari ini</div>
                        <div class="text-xs text-slate-400 mt-1">Hari {{ ucfirst($currentDay) }} tidak ada pelajaran yang terjadwal.</div>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
