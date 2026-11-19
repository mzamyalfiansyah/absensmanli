@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')
@section('page_title', 'Kelola Akun Pengguna & Administrator')

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- Header Action Bar -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Akun Pengguna & Administrator</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola hak akses 5 role administrator dan 2 role pengguna (Guru
                & Siswa).</p>
        </div>
        <button type="button" onclick="openCreateUserModal()"
            class="w-full sm:w-auto px-4 py-3 sm:py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-user-plus mr-2"></i> Tambah Pengguna Baru
        </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.users.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Cari Pengguna</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama / Username / Email..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Filter Role</label>
                <select name="role"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Role</option>
                    @foreach($roles as $key => $lbl)
                    <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Status Akun</label>
                <select name="status"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit"
                    class="flex-1 py-2.5 sm:py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="flex-1 lg:flex-none py-2.5 sm:py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">

        {{-- TAMPILAN HP & TABLET KECIL: kartu per pengguna (disembunyikan di layar >= md) --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($users as $u)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 text-sm break-words">{{ $u->name }}</div>
                        <div class="text-[11px] text-slate-400 break-all">{{ $u->email ?? '-' }}</div>
                    </div>
                    <span
                        class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
                    <div>
                        <dt class="text-slate-400 text-[11px]">Username / ID</dt>
                        <dd class="font-mono font-medium text-slate-700 break-all">{{ $u->username }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 text-[11px]">Role Akses</dt>
                        <dd>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $u->role_display_name }}
                            </span>
                        </dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-400 text-[11px]">Ganti Password?</dt>
                        <dd>
                            @if($u->must_change_password)
                            <span class="text-amber-600 font-bold text-[11px]"><i
                                    class="fa-solid fa-clock-rotate-left mr-1"></i> Wajib</span>
                            @else
                            <span class="text-emerald-600 text-[11px]"><i class="fa-solid fa-check mr-1"></i>
                                Sudah</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="flex items-center gap-2 pt-1">
                    <button type="button" data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                        onclick="openResetPasswordModal(this.dataset.id, this.dataset.name)"
                        class="flex-1 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-blue-600 transition text-xs font-semibold">
                        <i class="fa-solid fa-key mr-1"></i> Reset Password
                    </button>

                    @if($u->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle', $u->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" data-msg="Ubah status aktif akun {{ $u->name }}?"
                            onclick="return confirm(this.dataset.msg)"
                            class="w-full py-2 rounded-lg border border-slate-200 {{ $u->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition text-xs font-semibold">
                            <i class="fa-solid {{ $u->is_active ? 'fa-user-slash' : 'fa-user-check' }} mr-1"></i>
                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-10 px-4 text-center text-xs text-slate-400">Tidak ada pengguna yang sesuai dengan filter.
            </div>
            @endforelse
        </div>

        {{-- TAMPILAN DESKTOP: tabel (hanya muncul di layar >= md) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Username / ID</th>
                        <th class="py-3 px-4">Role Akses</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Ganti Password?</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $u->email ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 font-mono font-medium text-slate-700">{{ $u->username }}</td>
                        <td class="py-3 px-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $u->role_display_name }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($u->must_change_password)
                            <span class="text-amber-600 font-bold text-[11px]"><i
                                    class="fa-solid fa-clock-rotate-left mr-1"></i> Wajib</span>
                            @else
                            <span class="text-emerald-600 text-[11px]"><i class="fa-solid fa-check mr-1"></i>
                                Sudah</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                            <button type="button" data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                                onclick="openResetPasswordModal(this.dataset.id, this.dataset.name)"
                                title="Reset Password"
                                class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-blue-600 transition">
                                <i class="fa-solid fa-key"></i>
                            </button>

                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $u->id) }}" class="inline">
                                @csrf
                                <button type="submit" data-msg="Ubah status aktif akun {{ $u->name }}?"
                                    onclick="return confirm(this.dataset.msg)"
                                    title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}"
                                    class="p-1.5 rounded-lg border border-slate-200 {{ $u->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition">
                                    <i class="fa-solid {{ $u->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">Tidak ada pengguna yang sesuai dengan
                            filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 overflow-x-auto">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna Baru -->
<div id="createUserModal"
    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-4">
    <div
        class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-5 sm:p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
            <button type="button" onclick="closeCreateUserModal()" class="p-1 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap</label>
                <input type="text" name="name" required
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Username / NIS / NIP</label>
                    <input type="text" name="username" required placeholder="Contoh: 20261009"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Email (Opsional)</label>
                    <input type="email" name="email" placeholder="user@sman5karawang.sch.id"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Role Pengguna</label>
                    <select name="role" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs focus:border-blue-600 focus:outline-none">
                        @foreach($roles as $k => $l)
                        <option value="{{ $k }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Password Awal</label>
                    <input type="text" name="password" value="smanli2026" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs font-mono focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 pt-2">
                <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded text-blue-600">
                    <span>Akun Langsung Aktif</span>
                </label>
                <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="must_change_password" value="1" checked class="rounded text-blue-600">
                    <span>Wajib Ganti Password Awal</span>
                </label>
            </div>

            <div
                class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateUserModal()"
                    class="px-4 py-2.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 sm:py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan
                    Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="resetPasswordModal"
    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-4">
    <div
        class="bg-white rounded-2xl max-w-sm w-full max-h-[90vh] overflow-y-auto p-5 sm:p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900 break-words" id="resetModalTitle">Reset Password</h3>
            <button type="button" onclick="closeResetPasswordModal()" class="p-1 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="resetPasswordForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Password Baru</label>
                <input type="text" name="new_password" placeholder="Kosongkan untuk password default sistem"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 sm:py-2 text-base sm:text-xs font-mono focus:border-blue-600 focus:outline-none">
                <span class="text-[11px] text-slate-400 mt-1 block">Pengguna akan diwajibkan mengganti password ini
                    saat login.</span>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2">
                <button type="button" onclick="closeResetPasswordModal()"
                    class="px-4 py-2.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit"
                    class="px-4 py-2.5 sm:py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white">Reset
                    Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateUserModal() {
    document.getElementById('createUserModal').classList.remove('hidden');
}

function closeCreateUserModal() {
    document.getElementById('createUserModal').classList.add('hidden');
}

function openResetPasswordModal(userId, userName) {
    document.getElementById('resetModalTitle').innerText = 'Reset Password: ' + userName;
    document.getElementById('resetPasswordForm').action = '/admin/users/' + userId + '/reset-password';
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}
</script>
@endsection