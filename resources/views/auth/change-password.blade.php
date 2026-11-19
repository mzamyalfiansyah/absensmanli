@extends('layouts.auth')

@section('title', 'Perbarui Password')

@section('content')
<div class="mb-4">
    <div class="inline-flex items-center space-x-2 text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg text-xs font-semibold">
        <i class="fa-solid fa-shield-halved"></i>
        <span>Penggantian Password Wajib</span>
    </div>
    <h2 class="text-lg font-bold text-slate-800 mt-2">Buat Password Baru Anda</h2>
    <p class="text-xs text-slate-500 mt-1">
        @if(auth()->user()->must_change_password)
            Untuk menjaga keamanan akun Anda, silakan ubah password awal (default) menjadi password baru yang hanya Anda ketahui.
        @else
            Silakan masukkan password saat ini dan tentukan password baru Anda.
        @endif
    </p>
</div>

<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf

    @if(!auth()->user()->must_change_password)
    <div>
        <label for="current_password" class="block text-sm font-semibold text-slate-700">Password Saat Ini</label>
        <div class="relative mt-1">
            <input type="password" id="current_password" name="current_password" required
                class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 @error('current_password') border-rose-500 @enderror">
        </div>
        @error('current_password')
            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror
    </div>
    @endif

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700">Password Baru</label>
        <div class="relative mt-1">
            <input type="password" id="password" name="password" required minlength="6"
                placeholder="Minimal 6 karakter"
                class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20 @error('password') border-rose-500 @enderror">
        </div>
        @error('password')
            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
        <div class="relative mt-1">
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                placeholder="Ulangi password baru"
                class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
        </div>
    </div>

    <div class="pt-2">
        <button type="submit"
            class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-blue-600/30 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
            <i class="fa-solid fa-key mr-2"></i> Simpan Password Baru
        </button>
    </div>
</form>

<div class="mt-4 text-center">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i class="fa-solid fa-arrow-left mr-1"></i> Batal dan Keluar
        </button>
    </form>
</div>
@endsection
