@extends('layouts.app')

@section('title', 'Pelanggaran Siswa Perwalian')
@section('page_title', 'Catatan Kedisiplinan Siswa ' . ($class?->name ?? 'Semua Kelas'))

@section('content')
<div class="space-y-6">

    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">
                    Catatan Pelanggaran Siswa
                    {{ $class?->name ?? 'Semua Kelas' }}
                </h2>

                <p class="text-xs text-slate-500 mt-1">
                    Wali kelas memantau perkembangan kedisiplinan dan poin
                    pelanggaran peserta didik perwalian.
                </p>
            </div>

            <a href="{{ route('walas.attendance') }}" class="px-4 py-2 rounded-xl border border-slate-300
                       text-slate-700 hover:bg-slate-50 text-xs
                       font-bold transition whitespace-nowrap">
                &larr; Kembali ke Presensi
            </a>
        </div>


        @if($isAdmin)
        <form method="GET" action="{{ request()->url() }}"
            class="mt-5 flex flex-col sm:flex-row sm:items-center gap-3 text-xs">

            <select name="class_id" id="class_id" class="w-full sm:w-52 h-10 rounded-xl border border-slate-300
                       px-3 text-sm text-slate-700 bg-white
                       focus:border-blue-500 focus:outline-none">
                <option value="">Semua Kelas</option>

                @foreach($classes as $item)
                <option value="{{ $item->id }}" @selected((string) request('class_id')===(string) $item->id)>
                    {{ $item->name }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex h-10 w-fit shrink-0 items-center justify-center
                       px-4 rounded-xl bg-slate-800 text-white text-xs font-bold
                       hover:bg-slate-900 cursor-pointer transition whitespace-nowrap">
                <i class="fa-solid fa-filter mr-1"></i>
                Terapkan
            </button>

            <a href="{{ request()->url() }}" class="inline-flex h-10 w-fit shrink-0 items-center justify-center
                  px-4 rounded-xl border border-slate-300 text-slate-600
                  hover:bg-slate-50 transition whitespace-nowrap">
                Reset
            </a>

        </form>
        @endif

    </div>

    {{-- Tabel Pelanggaran --}}
    <div class="bg-white rounded-2xl border border-slate-200
                shadow-xs overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">

                <thead class="bg-slate-50 text-slate-500
                              font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Jenis Pelanggaran</th>
                        <th class="py-3 px-4">Poin</th>
                        <th class="py-3 px-4">Kronologi</th>
                        <th class="py-3 px-4">Tindak Lanjut</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($violations as $v)
                    <tr class="hover:bg-slate-50/50 transition">

                        <td class="py-3 px-4 whitespace-nowrap
                                       font-medium text-slate-600">
                            {{ $v->incident_date->format('d/m/Y') }}
                        </td>

                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $v->student?->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 text-slate-800">
                            {{ $v->violationType?->name ?? '-' }}
                        </td>

                        <td class="py-3 px-4 font-bold text-rose-600">
                            +{{ $v->points }}
                        </td>

                        <td class="py-3 px-4 text-slate-600 max-w-xs">
                            {{ $v->chronology }}
                        </td>

                        <td class="py-3 px-4 text-slate-600">
                            {{ $v->follow_up_action ?? '-' }}
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            {!! $v->status_badge !!}
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 px-4 text-center text-slate-400">
                            Tidak ada catatan pelanggaran
                            {{ $class?->name
                                    ? 'pada kelas ' . $class->name
                                    : 'pada semua kelas' }}.
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination tetap mempertahankan filter --}}
        <div class="p-4 border-t border-slate-200">
            {{ $violations->links() }}
        </div>

    </div>

</div>
@endsection