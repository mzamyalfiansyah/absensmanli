@extends('layouts.app')

@php
$isSuperAdmin = $isSuperAdmin ?? auth()->user()->role === 'superadmin';
@endphp

@section('title', 'Catatan Kedisiplinan')
@section('page_title', 'Catatan Kedisiplinan Siswa')

@section('content')
<div class="space-y-6">

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">
                {{ $isSuperAdmin ? 'Rekap Kedisiplinan Siswa' : 'Catatan Kedisiplinan & Poin Siswa' }}
            </h2>

            @if($isSuperAdmin)
            <p class="text-xs text-slate-500 mt-0.5">
                Pantau catatan pelanggaran seluruh siswa atau pilih kelas dan nama tertentu.
            </p>
            @else
            <p class="text-xs text-slate-500 mt-0.5">
                NIS: {{ $student->nis }}
                &bull; Nama: {{ $student->name }}
                &bull; Kelas: {{ $student->schoolClass->name ?? '-' }}
            </p>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            @if(!$isSuperAdmin)
            <span
                class="px-3.5 py-1.5 rounded-xl font-black text-xs {{ $totalPoints > 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $isSuperAdmin ? 'Total Poin Hasil Filter:' : 'Total Akumulasi:' }}
                {{ $totalPoints }} Poin
            </span>
            @endif
        </div>
    </div>

    @if($isSuperAdmin)
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('siswa.violations') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">

            <div>
                <label for="class_id" class="block text-slate-500 mb-1 font-semibold">
                    Kelas
                </label>

                <select name="class_id" id="class_id"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs">

                    <option value="">Semua Kelas</option>

                    @foreach($classes as $class)
                    <option value="{{ $class->id }}"
                        {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label for="student_name" class="block text-slate-500 mb-1 font-semibold">
                    Nama Siswa
                </label>

                <input type="text" name="student_name" id="student_name" value="{{ request('student_name') }}"
                    placeholder="Cari nama siswa..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition cursor-pointer">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Filter
                </button>

                <a href="{{ route('siswa.violations') }}"
                    class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>
    @endif

    @if($violations->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
        <div
            class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-2xl mb-3">
            <i class="fa-solid fa-award"></i>
        </div>

        <h3 class="text-base font-bold text-slate-800">
            @if($isSuperAdmin && request()->filled('class_id'))
            Tidak Ada Pelanggaran di Kelas Ini
            @elseif($isSuperAdmin && request()->filled('student_name'))
            Tidak Ada Catatan Pelanggaran untuk Siswa Ini
            @elseif($isSuperAdmin)
            Belum Ada Catatan Pelanggaran
            @else
            Tidak Ada Catatan Pelanggaran
            @endif
        </h3>

        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
            @if($isSuperAdmin && request()->filled('class_id'))
            Tidak ditemukan catatan pelanggaran pada kelas yang dipilih.
            Coba pilih kelas lain atau klik Reset untuk melihat semua kelas.
            @elseif($isSuperAdmin && request()->filled('student_name'))
            Tidak ditemukan catatan pelanggaran yang sesuai dengan nama siswa tersebut.
            Coba gunakan nama lain atau klik Reset.
            @elseif($isSuperAdmin)
            Belum ada catatan pelanggaran yang tercatat untuk seluruh siswa.
            @else
            Anda belum memiliki catatan pelanggaran tata tertib sekolah.
            Pertahankan kedisiplinan dan prestasi Anda.
            @endif
        </p>
    </div>
    @else

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">

                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal Kejadian</th>

                        @if($isSuperAdmin)
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        @endif

                        <th class="py-3 px-4">Pelanggaran</th>
                        <th class="py-3 px-4">Poin</th>
                        <th class="py-3 px-4">Kronologi</th>
                        <th class="py-3 px-4">Tindak Lanjut</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach($violations as $v)
                    <tr class="hover:bg-slate-50/50 transition">

                        <td class="py-3 px-4 whitespace-nowrap font-medium text-slate-600">
                            {{ \Carbon\Carbon::parse($v->incident_date)->format('d/m/Y') }}
                        </td>

                        @if($isSuperAdmin)
                        <td class="py-3 px-4 whitespace-nowrap text-slate-700">
                            {{ $v->student->nis ?? '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap font-bold text-slate-900">
                            {{ $v->student->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap text-slate-700">
                            {{ $v->student->schoolClass->name ?? '-' }}
                        </td>
                        @endif

                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $v->violationType->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap font-extrabold text-rose-600">
                            +{{ $v->points }}
                        </td>

                        <td class="py-3 px-4 text-slate-600 max-w-xs">
                            {{ $v->chronology ?? '-' }}
                        </td>

                        <td class="py-3 px-4 text-slate-600">
                            {{ $v->follow_up_action ?? '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            {!! $v->status_badge !!}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $violations->links() }}
        </div>
    </div>
    @endif

</div>
@endsection