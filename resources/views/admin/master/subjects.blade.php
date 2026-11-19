@extends('layouts.app')

@section('title', 'Mata Pelajaran')
@section('page_title', 'Kelola Data Mata Pelajaran')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Mata Pelajaran SMAN 5 Karawang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola kode dan nama mata pelajaran kurikulum merdeka.</p>
        </div>
        <button type="button" onclick="openCreateSubjectModal()"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Mata Pelajaran
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kode Mapel</th>
                        <th class="py-3 px-4">Nama Mata Pelajaran</th>
                        <th class="py-3 px-4">Deskripsi / Kategori</th>
                        <th class="py-3 px-4">Jumlah Jadwal KBM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subjects as $sb)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-blue-700">{{ $sb->code }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $sb->name }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $sb->description ?? '-' }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-700">{{ $sb->schedules_count }} Jadwal</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">Belum ada mata pelajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Mapel -->
<div id="createSubjectModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Mata Pelajaran</h3>
            <button type="button" onclick="closeCreateSubjectModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.subjects.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Kode Mata Pelajaran</label>
                <input type="text" name="code" required placeholder="Contoh: MTK-WJB"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono uppercase focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Mata Pelajaran</label>
                <input type="text" name="name" required placeholder="Contoh: Matematika Wajib"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Deskripsi (Opsional)</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateSubjectModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateSubjectModal() { document.getElementById('createSubjectModal').classList.remove('hidden'); }
function closeCreateSubjectModal() { document.getElementById('createSubjectModal').classList.add('hidden'); }
</script>
@endsection
