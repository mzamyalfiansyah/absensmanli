@extends('layouts.app')

@section('title', 'Data Guru')
@section('page_title', 'Kelola Data Guru Pengajar')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Guru Pengajar SMAN 5 Karawang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data pendidik, bidang studi, serta impor/ekspor data massal via Excel atau CSV.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openImportModal()"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-file-import mr-1.5 text-blue-600"></i> Impor CSV
            </button>
            <a href="{{ route('admin.master.teachers.export') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-file-export mr-1.5 text-emerald-600"></i> Ekspor CSV
            </a>
            <button type="button" onclick="openCreateTeacherModal()"
                class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center">
                <i class="fa-solid fa-plus mr-1.5"></i> Tambah Guru
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.master.teachers') }}" class="flex gap-3 text-xs">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan NIP, Nama Guru, atau Bidang Studi..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">NIP</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">L/P</th>
                        <th class="py-3 px-4">Bidang Studi</th>
                        <th class="py-3 px-4">No. HP</th>
                        <th class="py-3 px-4">Status Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teachers as $t)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-mono font-medium text-slate-700">{{ $t->nip }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $t->name }}</td>
                            <td class="py-3 px-4">{{ $t->gender }}</td>
                            <td class="py-3 px-4 text-blue-700 font-medium">{{ $t->specialization ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $t->phone ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ ($t->user->is_active ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ ($t->user->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">Belum ada data guru pengajar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $teachers->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div id="createTeacherModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Guru Baru</h3>
            <button type="button" onclick="closeCreateTeacherModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.teachers.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap & Gelar</label>
                <input type="text" name="name" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">NIP</label>
                    <input type="text" name="nip" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Jenis Kelamin</label>
                    <select name="gender" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Bidang Studi (Mapel)</label>
                    <input type="text" name="specialization" placeholder="Contoh: Matematika" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">No. WhatsApp / HP</label>
                    <input type="text" name="phone" placeholder="08..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Password Awal Akun</label>
                    <input type="text" name="password" value="guru12345" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateTeacherModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan Data Guru</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor CSV Guru -->
<div id="importModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Impor Guru via CSV</h3>
            <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.teachers.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih File CSV (.csv)</label>
                <input type="file" name="file" accept=".csv" required
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Format Kolom Header CSV:</div>
                <div class="font-mono bg-white p-1.5 rounded border border-slate-200 text-[10px] break-all">
                    nip,nama,jenis_kelamin,bidang_studi,no_hp,alamat,email
                </div>
                <div class="text-[10px] text-slate-500">*Password awal otomatis diset dan wajib diganti saat guru pertama kali login.</div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateTeacherModal() { document.getElementById('createTeacherModal').classList.remove('hidden'); }
function closeCreateTeacherModal() { document.getElementById('createTeacherModal').classList.add('hidden'); }
function openImportModal() { document.getElementById('importModal').classList.remove('hidden'); }
function closeImportModal() { document.getElementById('importModal').classList.add('hidden'); }
</script>
@endsection
