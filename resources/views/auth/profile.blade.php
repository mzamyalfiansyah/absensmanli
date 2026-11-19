@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page_title', 'Profil Pengguna')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Profile Card Header -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-2xl shadow-lg shadow-blue-500/20">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $user->username }} &bull; {{ $user->role_display_name }}</p>
                </div>
                <span class="mt-2 sm:mt-0 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    <span class="w-2 h-2 rounded-full mr-1.5 {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ $user->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                </span>
            </div>

            @if($user->student)
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg">Kelas: <strong>{{ $user->student->schoolClass->name ?? '-' }}</strong></span>
                    <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg">NIS: <strong>{{ $user->student->nis }}</strong></span>
                    <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg">NISN: <strong>{{ $user->student->nisn ?? '-' }}</strong></span>
                </div>
            @endif

            @if($user->teacher)
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg">NIP: <strong>{{ $user->teacher->nip }}</strong></span>
                    <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-lg">Bidang Studi: <strong>{{ $user->teacher->specialization ?? '-' }}</strong></span>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Profile & Security -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Form Update Data Pribadi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-user-pen mr-2 text-blue-600"></i> Informasi Kontak & Profil
            </h3>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Keamanan Password -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 mb-3 flex items-center">
                    <i class="fa-solid fa-lock mr-2 text-emerald-600"></i> Keamanan Kata Sandi
                </h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Pastikan password Anda tetap rahasia dan tidak dibagikan kepada siapapun. Anda dapat mengganti password kapan saja melalui tombol di bawah.
                </p>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 space-y-2 mb-6">
                    <div class="flex items-center text-slate-700">
                        <i class="fa-solid fa-check text-emerald-600 mr-2"></i> Menggunakan Hashing Enkripsi Standar Laravel
                    </div>
                    <div class="flex items-center text-slate-700">
                        <i class="fa-solid fa-check text-emerald-600 mr-2"></i> Proteksi Terhadap Serangan Brute Force
                    </div>
                </div>
            </div>

            <a href="{{ route('password.change') }}" class="w-full py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 text-center transition flex items-center justify-center">
                <i class="fa-solid fa-key mr-2 text-slate-500"></i> Ganti Password Akun
            </a>
        </div>
    </div>
</div>
@endsection
