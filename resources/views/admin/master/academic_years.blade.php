@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('page_title', 'Kelola Tahun Ajaran & Semester')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Tahun Ajaran</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tentukan tahun ajaran dan semester aktif untuk seluruh jadwal KBM dan presensi.</p>
        </div>
        <button type="button" onclick="openCreateYearModal()"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Tahun Ajaran
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Tahun Ajaran</th>
                        <th class="py-3 px-4">Semester</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Jumlah Kelas</th>
                        <th class="py-3 px-4">Jumlah Jadwal</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($academicYears as $y)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $y->name }}</td>
                            <td class="py-3 px-4 uppercase font-semibold text-slate-700">{{ $y->semester }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $y->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $y->is_active ? 'Aktif Saat Ini' : 'Arsip' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $y->classes_count }} Kelas</td>
                            <td class="py-3 px-4 text-slate-600">{{ $y->schedules_count }} Jadwal</td>
                            <td class="py-3 px-4 text-right">
                                @if(!$y->is_active)
                                    <form method="POST" action="{{ route('admin.master.academic_years.set_active', $y->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-[11px] transition">
                                            Jadikan Aktif
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-emerald-600 font-bold"><i class="fa-solid fa-check-circle mr-1"></i> Aktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada tahun ajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Tahun Ajaran -->
<div id="createYearModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Tahun Ajaran</h3>
            <button type="button" onclick="closeCreateYearModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.academic_years.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Tahun Ajaran</label>
                <input type="text" name="name" required placeholder="Contoh: 2026/2027"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Semester</label>
                <select name="semester" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="ganjil">Ganjil</option>
                    <option value="genap">Genap</option>
                </select>
            </div>

            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer pt-1">
                <input type="checkbox" name="is_active" value="1" checked class="rounded text-blue-600">
                <span>Jadikan Tahun Ajaran Aktif</span>
            </label>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateYearModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateYearModal() { document.getElementById('createYearModal').classList.remove('hidden'); }
function closeCreateYearModal() { document.getElementById('createYearModal').classList.add('hidden'); }
</script>
@endsection
