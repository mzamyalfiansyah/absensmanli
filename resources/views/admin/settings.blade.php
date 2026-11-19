@extends('layouts.app')

@section('title', 'Pengaturan GPS & Sistem')
@section('page_title', 'Pengaturan Titik Koordinat & Validasi Lokasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/10 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h2 class="text-xl font-bold">Validasi Berlapis Lokasi & Geofencing</h2>
            <p class="text-xs text-blue-100 mt-1">
                Atur koordinat pusat SMAN 5 Karawang dan radius toleransi presensi siswa. Perhitungan jarak menggunakan formula Haversine di server.
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white">
                <i class="fa-solid fa-satellite-dish mr-1.5 text-emerald-300"></i> Server-Side Geofencing
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        <!-- Koordinat & Radius GPS -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center">
                <i class="fa-solid fa-map-location-dot mr-2 text-rose-600"></i> Koordinat Titik Sekolah & Radius Presensi
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Latitude Sekolah</label>
                    <input type="text" name="school_latitude" value="{{ old('school_latitude', $settings->school_latitude) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-600 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Contoh Karawang: -6.3053800</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Longitude Sekolah</label>
                    <input type="text" name="school_longitude" value="{{ old('school_longitude', $settings->school_longitude) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-600 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Contoh Karawang: 107.3012500</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Radius Diizinkan (Meter)</label>
                    <div class="relative mt-1">
                        <input type="number" name="allowed_radius_meters" value="{{ old('allowed_radius_meters', $settings->allowed_radius_meters) }}" min="10" max="5000" required
                            class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm pr-16 focus:border-blue-600 focus:outline-none">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-400 pointer-events-none">Meter</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Disarankan: 100 meter dari titik tengah sekolah.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Batas Toleransi Akurasi GPS (Meter)</label>
                    <div class="relative mt-1">
                        <input type="number" name="max_gps_accuracy_meters" value="{{ old('max_gps_accuracy_meters', $settings->max_gps_accuracy_meters) }}" min="5" max="200" required
                            class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm pr-16 focus:border-blue-600 focus:outline-none">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-400 pointer-events-none">Meter</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Sinyal GPS di atas batas ini dianggap kurang akurat.</span>
                </div>
            </div>

            <!-- Kebijakan Validasi Lokasi -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3 mt-4">
                <label class="flex items-center space-x-2 text-xs font-bold text-slate-800 cursor-pointer">
                    <input type="checkbox" name="require_location" value="1" {{ $settings->require_location ? 'checked' : '' }} class="rounded text-blue-600">
                    <span>Wajibkan Izin & Validasi Lokasi saat Scan Presensi</span>
                </label>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan jika Izin Lokasi Gagal / Akurasi Buruk / di Luar Radius:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <label class="p-3 bg-white rounded-xl border border-slate-200 flex items-start space-x-2 cursor-pointer hover:border-blue-300">
                            <input type="radio" name="fallback_on_location_error" value="need_verification" {{ $settings->fallback_on_location_error === 'need_verification' ? 'checked' : '' }} class="mt-0.5 text-blue-600">
                            <div>
                                <strong class="text-slate-800 block">Tandai "Perlu Verifikasi"</strong>
                                <span class="text-slate-500 text-[11px]">Presensi tetap dicatat namun ditandai untuk diperiksa guru / administrator.</span>
                            </div>
                        </label>
                        <label class="p-3 bg-white rounded-xl border border-slate-200 flex items-start space-x-2 cursor-pointer hover:border-blue-300">
                            <input type="radio" name="fallback_on_location_error" value="reject" {{ $settings->fallback_on_location_error === 'reject' ? 'checked' : '' }} class="mt-0.5 text-rose-600">
                            <div>
                                <strong class="text-slate-800 block">Tolak Presensi</strong>
                                <span class="text-slate-500 text-[11px]">Siswa tidak dapat absen jika berada di luar radius atau tanpa izin GPS.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Identitas Sekolah & Default Akun -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center">
                <i class="fa-solid fa-building-columns mr-2 text-blue-600"></i> Identitas Sekolah & Akun Default
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nama Sekolah</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">NPSN</label>
                    <input type="text" name="school_npsn" value="{{ old('school_npsn', $settings->school_npsn) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                <textarea name="school_address" rows="2" required
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">{{ old('school_address', $settings->school_address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nama Kepala Sekolah</label>
                    <input type="text" name="headmaster_name" value="{{ old('headmaster_name', $settings->headmaster_name) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">NIP Kepala Sekolah</label>
                    <input type="text" name="headmaster_nip" value="{{ old('headmaster_nip', $settings->headmaster_nip) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Password Awal Siswa Baru</label>
                    <input type="text" name="default_student_password" value="{{ old('default_student_password', $settings->default_student_password) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-600 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Siswa wajib ganti saat pertama kali login.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Durasi Sesi QR Default (Menit)</label>
                    <input type="number" name="default_session_minutes" value="{{ old('default_session_minutes', $settings->default_session_minutes) }}" min="10" max="240" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/30 transition flex items-center">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Seluruh Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
