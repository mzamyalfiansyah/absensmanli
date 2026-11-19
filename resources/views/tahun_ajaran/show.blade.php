@extends('layouts.app')

@section('title', 'Tahun Ajaran ' . $name)
@section('page_title', 'Tahun Ajaran ' . $name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Tahun Ajaran {{ $name }}</h2>
            @if($year)
            <p class="text-xs text-slate-500 mt-0.5">Semester {{ ucfirst($year->semester) }}</p>
            @endif
        </div>
        @if($isActive)
        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs">Aktif</span>
        @endif
    </div>

    @if(!$stats)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 p-5 rounded-2xl text-sm">
        Belum ada data untuk tahun ajaran {{ $name }}.
        @if(auth()->user()->isSuperAdmin())
        Tambahkan lewat
        <a href="{{ route('admin.master.academic_years') }}" class="font-semibold underline">Master Tahun Ajaran</a>.
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach([['Kelas', $stats['classes']], ['Siswa', $stats['students']], ['Jadwal', $stats['schedules']]] as [$label, $value])
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500 font-semibold">{{ $label }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($value) }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <h3 class="text-sm font-bold text-slate-900 mb-3">Rekap Kehadiran Siswa</h3>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center text-xs">
            @foreach($stats['attendance'] as $status => $total)
            <div class="p-3 rounded-xl bg-slate-50">
                <p class="text-slate-500 font-semibold uppercase">{{ $status }}</p>
                <p class="text-lg font-bold text-slate-900">{{ number_format($total) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection