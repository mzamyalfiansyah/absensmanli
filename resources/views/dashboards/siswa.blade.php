@extends('layouts.app')

@section('title', 'Dashboard Siswa')
@section('page_title', 'Dashboard Siswa')

@section('content')
<div class="space-y-6">
    @if(!$student)
        <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-sm">
            <i class="fa-solid fa-circle-exclamation mr-2 text-amber-600"></i>
            Akun Anda belum terhubung dengan data Siswa. Hubungi Administrator sekolah.
        </div>
    @else
        <!-- Header Info Siswa & Quick Scan Action -->
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-800 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/10 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div>
                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white uppercase tracking-wider">Portal Siswa</span>
                <h2 class="text-2xl font-black tracking-tight mt-1">{{ $student->name }}</h2>
                <p class="text-xs text-blue-100 mt-1">
                    Kelas: <strong>{{ $student->schoolClass->name ?? '-' }}</strong> &bull; NIS: <strong>{{ $student->nis }}</strong>
                </p>
            </div>
            <div class="w-full md:w-auto">
                <a href="{{ route('siswa.scan') }}" class="w-full sm:w-auto justify-center px-5 py-3 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-extrabold transition flex items-center shadow-lg shadow-blue-900/20 group">
                    <i class="fa-solid fa-camera mr-2 text-indigo-600 group-hover:scale-110 transition"></i> Buka Kamera Scan QR Absen
                </a>
            </div>
        </div>

        <!-- Stat Ringkasan Kehadiran & Poin Disiplin -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Hadir</div>
                <div class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['hadir'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Sesi Tepat Waktu</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Terlambat</div>
                <div class="text-2xl font-extrabold text-amber-700 mt-0.5">{{ $stats['terlambat'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">> 15 Menit</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Izin</div>
                <div class="text-2xl font-extrabold text-blue-700 mt-0.5">{{ $stats['izin'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Izin Tertulis</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider">Sakit</div>
                <div class="text-2xl font-extrabold text-purple-700 mt-0.5">{{ $stats['sakit'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Surat Dokter</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
                <div class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Alpa</div>
                <div class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ $stats['alpa'] }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Tanpa Keterangan</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs col-span-2 lg:col-span-1">
                <div class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider">Poin Pelanggaran</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total_points'] }}</div>
                <div class="text-[10px] {{ $stats['total_points'] > 0 ? 'text-rose-500 font-semibold' : 'text-slate-400' }} mt-0.5">
                    {{ $stats['total_points'] > 0 ? 'Perlu pembinaan' : 'Disiplin baik' }}
                </div>
            </div>
        </div>

        <!-- Jadwal Pelajaran Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-calendar-day mr-2 text-blue-600"></i> Jadwal Pelajaran Hari Ini ({{ ucfirst($currentDay) }})
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pastikan Anda berada di area sekolah saat memindai QR Code.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($todaySchedules as $sch)
                    @php
                        $myAtt = $myAttendancesToday[$sch->id] ?? null;
                        $session = $activeSessions[$sch->id] ?? null;
                    @endphp
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">{{ $sch->period_label }}</span>
                                <span class="text-xs font-medium text-slate-500">{{ $sch->time_range }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sch->subject->name ?? '-' }}</span>
                            </div>
                            <div class="text-xs text-slate-600">
                                Guru: <strong>{{ $sch->teacher->name ?? '-' }}</strong>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                <span class="text-slate-500">Status Kehadiran:</span>
                                @if($myAtt)
                                    {!! $myAtt->status_badge !!}
                                    <span class="text-slate-400">&bull;</span>
                                    {!! $myAtt->location_status_badge !!}
                                    @if($myAtt->scanned_at)
                                        <span class="text-[11px] text-slate-400">({{ $myAtt->scanned_at->format('H:i') }})</span>
                                    @endif
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Belum Absen</span>
                                @endif
                            </div>
                        </div>

                        <div class="w-full sm:w-auto flex sm:justify-end">
                            @if($myAtt)
                                <span class="w-full sm:w-auto justify-center inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-check mr-1.5"></i> Sudah Absen
                                </span>
                            @elseif($session)
                                <a href="{{ route('siswa.scan') }}" class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-xs animate-pulse">
                                    <i class="fa-solid fa-camera mr-1.5"></i> Sesi Aktif! Scan Sekarang
                                </a>
                            @else
                                <span class="w-full sm:w-auto justify-center inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-100 text-slate-500">
                                    <i class="fa-solid fa-lock mr-1.5"></i> Sesi Belum Dibuka
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs text-slate-400">Tidak ada jadwal KBM kelas hari ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Catatan Pelanggaran Siswa (Jika Ada) -->
        @if($violations->count() > 0)
            <div class="bg-white rounded-2xl border border-rose-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-rose-900 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-2 text-rose-600"></i> Catatan Kedisiplinan Anda
                    </h3>
                    <span class="text-xs font-bold text-rose-600">Total Akumulasi: {{ $stats['total_points'] }} Poin</span>
                </div>
                <div class="divide-y divide-rose-100">
                    @foreach($violations as $v)
                        <div class="py-3 flex items-start justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-800">{{ $v->violationType->name ?? '-' }}</div>
                                <div class="text-slate-500 mt-0.5">
                                    Tanggal: {{ $v->incident_date->format('d/m/Y') }} &bull; Tindak Lanjut: {{ $v->follow_up_action ?? '-' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-rose-600">+{{ $v->points }} Poin</span>
                                <div class="mt-1">{!! $v->status_badge !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
