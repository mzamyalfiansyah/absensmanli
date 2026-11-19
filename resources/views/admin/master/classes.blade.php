@extends('layouts.app')

@section('title', 'Data Rombongan Belajar')
@section('page_title', 'Kelola Data Kelas & Wali Kelas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Rombongan Belajar (Kelas)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur nama kelas, penugasan Wali Kelas (AdminWalas), serta akun Admin Kelas.</p>
        </div>
        <button type="button" onclick="openCreateClassModal()"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Tambah Kelas Baru
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Kelas</th>
                        <th class="py-3 px-4">Tingkat</th>
                        <th class="py-3 px-4">Jurusan</th>
                        <th class="py-3 px-4">Wali Kelas</th>
                        <th class="py-3 px-4">Admin Kelas</th>
                        <th class="py-3 px-4">Jumlah Siswa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($classes as $c)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-bold text-blue-700 text-sm">{{ $c->name }}</td>
                            <td class="py-3 px-4">Kelas {{ $c->level }}</td>
                            <td class="py-3 px-4 font-medium text-slate-700">{{ $c->major }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $c->homeroomTeacher->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700">
                                    {{ $c->classAdminUser->name ?? 'Belum Ditugaskan' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-700">{{ $c->students_count }} Siswa</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">Belum ada kelas terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div id="createClassModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Kelas Baru</h3>
            <button type="button" onclick="closeCreateClassModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.classes.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Kelas</label>
                <input type="text" name="name" required placeholder="Contoh: X-3 atau XI MIPA 2"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Tingkat</label>
                    <select name="level" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        <option value="10">Kelas 10 (Fase E)</option>
                        <option value="11">Kelas 11 (Fase F)</option>
                        <option value="12">Kelas 12 (Fase F)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Peminatan / Jurusan</label>
                    <input type="text" name="major" value="Umum" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Wali Kelas (Guru)</label>
                <select name="homeroom_teacher_id" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Pilih Wali Kelas (Opsional)</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->nip }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Akun Admin Kelas (Ketua/Sekretaris)</label>
                <select name="class_admin_user_id" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Pilih Akun Admin Kelas (Opsional)</option>
                    @foreach($adminKelasUsers as $aku)
                        <option value="{{ $aku->id }}">{{ $aku->name }} ({{ $aku->username }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateClassModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan Kelas</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateClassModal() { document.getElementById('createClassModal').classList.remove('hidden'); }
function closeCreateClassModal() { document.getElementById('createClassModal').classList.add('hidden'); }
</script>
@endsection
