@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')


<form method="POST" action="{{ route('login.post') }}" class="space-y-5">
    @csrf

    <div>
        <label for="username" class="block text-sm font-semibold text-slate-700">Nomor Identitas / Username</label>
        <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                placeholder="NIS (Siswa), NIP (Guru), atau Username Admin"
                class="block w-full rounded-xl border border-slate-300 pl-10 pr-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 transition @error('username') border-rose-500 @enderror">
        </div>
        @error('username')
        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
        </div>
        <div class="relative mt-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fa-solid fa-lock"></i>
            </div>
            <input type="password" id="password" name="password" required placeholder="Masukkan password Anda"
                class="block w-full rounded-xl border border-slate-300 pl-10 pr-10 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 transition @error('password') border-rose-500 @enderror">
            <button type="button" onclick="togglePasswordVisibility()"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                <i class="fa-regular fa-eye" id="toggleIcon"></i>
            </button>
        </div>
        @error('password')
        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>Ingat sesi masuk ini</span>
        </label>
        <span class="text-xs text-slate-400">Wajib ganti password awal</span>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-blue-600/30 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
            <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Masuk Sekarang
        </button>
    </div>
</form>

<!-- Informasi Akun Demo & Bantuan -->
<div class="mt-6 pt-5 border-t border-slate-100">
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Akun Pengujian Demo</span>
        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">Klik untuk
            isi
            otomatis</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
        <button type="button" onclick="fillLogin('superadmin', 'admin123')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">SuperAdmin</div>
            <div class="text-[11px] text-slate-500">superadmin / admin123</div>
        </button>
        <button type="button" onclick="fillLogin('adminkurikulum', 'admin123')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Admin Kurikulum</div>
            <div class="text-[11px] text-slate-500">adminkurikulum / admin123</div>
        </button>
        <button type="button" onclick="fillLogin('adminkedisiplinan', 'admin123')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Admin Disiplin</div>
            <div class="text-[11px] text-slate-500">adminkedisiplinan / admin123</div>
        </button>
        <button type="button" onclick="fillLogin('adminwalas', 'admin123')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Admin Walas (X-1)</div>
            <div class="text-[11px] text-slate-500">adminwalas / admin123</div>
        </button>
        <button type="button" onclick="fillLogin('adminkelas', 'admin123')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Admin Kelas (X-1)</div>
            <div class="text-[11px] text-slate-500">adminkelas / admin123</div>
        </button>
        <button type="button" onclick="fillLogin('197508102005011002', 'guru12345')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Guru (NIP Mulyadi)</div>
            <div class="text-[11px] text-slate-500">NIP / guru12345</div>
        </button>
        <button type="button" onclick="fillLogin('20261001', 'smanli2026')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Siswa (NIS Arya)</div>
            <div class="text-[11px] text-slate-500">20261001 / smanli2026</div>
        </button>
        <button type="button" onclick="fillLogin('20261002', 'smanli2026')"
            class="text-left p-2 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
            <div class="font-semibold text-slate-800">Siswa (NIS Annisa)</div>
            <div class="text-[11px] text-slate-500">20261002 / smanli2026</div>
        </button>
    </div>
</div>

<script>
function fillLogin(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
}

function togglePasswordVisibility() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection