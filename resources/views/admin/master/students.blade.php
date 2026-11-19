@extends('layouts.app')

@section('title', 'Data Siswa')
@section('page_title', 'Kelola Data Siswa')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Siswa SMAN 5 Karawang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola siswa per kelas, akun presensi, serta impor/ekspor data massal via Excel atau CSV.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openImportModal()"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-file-import mr-1.5 text-blue-600"></i> Impor CSV
            </button>
            <a href="{{ route('admin.master.students.export', ['class_id' => request('class_id')]) }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-file-export mr-1.5 text-emerald-600"></i> Ekspor CSV
            </a>
            <button type="button" onclick="openCreateStudentModal()"
                class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center">
                <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah Siswa
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.master.students') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Cari Siswa</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="NIS / Nama Siswa..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Kelas</label>
                <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.master.students') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">L/P</th>
                        <th class="py-3 px-4">No. HP Ortu</th>
                        <th class="py-3 px-4">Akumulasi Poin</th>
                        <th class="py-3 px-4">Status Akun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $s)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-mono font-medium text-slate-700">{{ $s->nis }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $s->name }}</td>
                            <td class="py-3 px-4 font-semibold text-blue-700">{{ $s->schoolClass->name ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $s->gender }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $s->phone_parent ?? '-' }}</td>
                            <td class="py-3 px-4 font-bold {{ $s->total_violation_points > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $s->total_violation_points }} Poin
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ ($s->user->is_active ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ ($s->user->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Belum ada data siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $students->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div id="createStudentModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Data Siswa Baru</h3>
            <button type="button" onclick="closeCreateStudentModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.students.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap Siswa</label>
                <input type="text" name="name" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">NIS (Nomor Induk Siswa)</label>
                    <input type="text" name="nis" required placeholder="Contoh: 20261010" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono focus:border-blue-600 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Digunakan sebagai username login.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">NISN (Opsional)</label>
                    <input type="text" name="nisn" placeholder="00..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Kelas</label>
                    <select name="class_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Jenis Kelamin</label>
                    <select name="gender" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Nomor Telepon Orang Tua / Wali</label>
                <input type="text" name="phone_parent" placeholder="08..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Alamat Rumah (Opsional)</label>
                <textarea name="address" rows="2" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-[11px] text-blue-800">
                <i class="fa-solid fa-info-circle mr-1 text-blue-600"></i>
                Password awal siswa diset otomatis: <strong>smanli2026</strong> dan wajib diubah saat pertama kali masuk.
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeCreateStudentModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan Data Siswa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor CSV Siswa -->
<div id="importModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Impor Siswa via CSV</h3>
            <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.master.students.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih File CSV (.csv)</label>
                <input type="file" name="file" accept=".csv" required
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                <div class="font-bold text-slate-800">Format Kolom Header CSV:</div>
                <div class="font-mono bg-white p-1.5 rounded border border-slate-200 text-[10px] break-all">
                    nis,nama,kelas,jenis_kelamin,nisn,no_hp_ortu,alamat
                </div>
                <div class="text-[10px] text-slate-500">*Password default akun: <code>smanli2026</code>. Kelas otomatis dibuat bila belum tersedia.</div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateStudentModal() { document.getElementById('createStudentModal').classList.remove('hidden'); }
function closeCreateStudentModal() { document.getElementById('createStudentModal').classList.add('hidden'); }
function openImportModal() { document.getElementById('importModal').classList.remove('hidden'); }
function closeImportModal() { document.getElementById('importModal').classList.add('hidden'); }
</script>
@endsection
