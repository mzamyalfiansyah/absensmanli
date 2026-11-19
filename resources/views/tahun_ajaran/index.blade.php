@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('page_title', 'Tahun Ajaran')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <h2 class="text-base font-bold text-slate-900">Semua Tahun Ajaran</h2>
        <p class="text-xs text-slate-500 mt-0.5">Pilih tahun ajaran untuk melihat ringkasan datanya.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($options as $opt)
        <a href="{{ route('tahun_ajaran.show', $opt['slug']) }}"
            class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 hover:border-blue-300 transition">
            <span class="text-sm font-semibold text-slate-800">{{ $opt['label'] }}</span>
            @if($opt['status'] === 'aktif')
            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-xs font-semibold">Aktif</span>
            @elseif($opt['status'] === 'mendatang')
            <span class="text-xs text-slate-400">Mendatang</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endsection