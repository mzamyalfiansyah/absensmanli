@extends('layouts.app')

@section('title', 'Jenis Pelanggaran')
@section('page_title', 'Master Jenis & Poin Pelanggaran Siswa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Katalog Jenis Pelanggaran & Poin Tata Tertib</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar pelanggaran, kategori bobot (Ringan, Sedang, Berat), dan sanksi tindak lanjut standar.</p>
        </div>
        <button type="button" onclick="openCreateTypeModal()"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Jenis Pelanggaran
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Nama Pelanggaran</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Poin</th>
                        <th class="py-3 px-4">Tindak Lanjut Standar</th>
                        <th class="py-3 px-4">Jumlah Kasus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($types as $tp)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $tp->code }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $tp->name }}</td>
                            <td class="py-3 px-4">{!! $tp->category_badge !!}</td>
                            <td class="py-3 px-4 font-extrabold text-rose-600">+{{ $tp->points }}</td>
                            <td class="py-3 px-4 text-slate-600 max-w-sm">{{ $tp->default_action ?? '-' }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $tp->violations_count }} Kasus</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada jenis pelanggaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Jenis Pelanggaran -->
<div id="createTypeModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Jenis Pelanggaran Baru</h3>
            <button type="button" onclick="closeCreateTypeModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('kedisiplinan.types.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Kode Pelanggaran</label>
                <input type="text" name="code" required placeholder="Contoh: PEL-07"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono uppercase focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Pelanggaran</label>
                <input type="text" name="name" required placeholder="Nama pelanggaran..."
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Kategori</label>
                    <select name="category" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        <option value="ringan">Ringan</option>
                        <option value="sedang">Sedang</option>
                        <option value="berat">Berat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Poin Pelanggaran</label>
                    <input type="number" name="points" value="10" min="1" max="100" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Tindak Lanjut Standar</label>
                <textarea name="default_action" rows="2" placeholder="Tindakan pembinaan atau sanksi..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateTypeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateTypeModal() { document.getElementById('createTypeModal').classList.remove('hidden'); }
function closeCreateTypeModal() { document.getElementById('createTypeModal').classList.add('hidden'); }
</script>
@endsection
