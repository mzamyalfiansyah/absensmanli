@extends('layouts.app')

@php
$isSuperAdmin = $isSuperAdmin
?? in_array(auth()->user()->role, ['super_admin', 'super admin'], true);
@endphp

@section('title', $isSuperAdmin ? 'Riwayat Absensi Siswa' : 'Riwayat Absensi Saya')
@section('page_title', $isSuperAdmin ? 'Riwayat Presensi Siswa' : 'Riwayat Presensi Saya')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">
                {{ $isSuperAdmin ? 'Rekap Riwayat Presensi Siswa' : 'Riwayat Presensi Saya' }}
            </h2>

            @if($isSuperAdmin)
            <p class="text-xs text-slate-500 mt-0.5">
                Pantau riwayat presensi seluruh siswa atau pilih kelas tertentu.
            </p>
            @else
            <p class="text-xs text-slate-500 mt-0.5">
                NIS: {{ $student->nis }}
                &bull; Nama: {{ $student->name }}
                &bull; Kelas: {{ $student->schoolClass->name ?? '-' }}
            </p>
            @endif
        </div>

        {{-- Tombol scan hanya untuk siswa --}}
        @unless($isSuperAdmin)
        <a href="{{ route('siswa.scan') }}"
            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-camera mr-1.5"></i>
            Buka Kamera Scan QR
        </a>
        @endunless
    </div>

    {{-- Filters --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('siswa.history') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ $isSuperAdmin ? '5' : '4' }} gap-3 text-xs">

            {{-- Filter kelas khusus super admin --}}
            @if($isSuperAdmin)
            <div>
                <label for="class_id" class="block text-slate-500 mb-1 font-semibold">
                    Kelas
                </label>

                <select name="class_id" id="class_id"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>

                    @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                        {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label for="start_date" class="block text-slate-500 mb-1 font-semibold">
                    Tanggal Mulai
                </label>

                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label for="end_date" class="block text-slate-500 mb-1 font-semibold">
                    Tanggal Selesai
                </label>

                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label for="status" class="block text-slate-500 mb-1 font-semibold">
                    Status Presensi
                </label>

                <select name="status" id="status"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat
                    </option>
                    <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ request('status') === 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Filter
                </button>

                <a href="{{ route('siswa.history') }}"
                    class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Attendance Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">

                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>

                        @if($isSuperAdmin)
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        @endif

                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Guru</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Waktu Scan</th>
                        <th class="py-3 px-4">Status Lokasi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $at)
                    <tr class="hover:bg-slate-50/50 transition">

                        <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($at->date)->format('d/m/Y') }}
                        </td>

                        @if($isSuperAdmin)
                        <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                            {{ $at->student->nis ?? '-' }}
                        </td>

                        <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap">
                            {{ $at->student->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                            {{ $at->student->schoolClass->name ?? '-' }}
                        </td>
                        @endif

                        <td class="py-3 px-4 font-bold text-blue-700">
                            {{ $at->schedule->subject->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                            {{ $at->schedule->teacher->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            {!! $at->status_badge !!}
                        </td>

                        <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap">
                            {{ $at->scanned_at
                                    ? \Carbon\Carbon::parse($at->scanned_at)->format('H:i:s') . ' WIB'
                                    : '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            {!! $at->location_status_badge !!}

                            @if($at->distance_meters !== null)
                            <span class="text-[11px] text-slate-400">
                                ({{ $at->distance_meters }}m)
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 9 : 6 }}" class="py-10 text-center text-slate-400">
                            @if($isSuperAdmin)
                            Tidak ada data presensi yang sesuai dengan filter.
                            @else
                            Belum ada riwayat absensi yang tercatat.
                            @endif
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