@extends('layouts.app')

@section('title', 'Input Laporan Jadwal KBM')
@section('page_title', 'Form Laporan Kehadiran Guru & Kondisi Kelas')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
    {{-- Tombol kembali --}}
    <div>
        <a href="{{ route('admin_kelas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-indigo-600 transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Jadwal
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Form Laporan Kehadiran Guru & Kondisi Kelas</h2>
            <p class="text-xs text-slate-500 mt-0.5">Laporan ini wajib diisi oleh Admin Kelas dan akan diverifikasi oleh Admin Kurikulum.</p>
        </div>

        {{-- Metadata Jadwal (Read-only) --}}
        <div class="bg-slate-50 mx-5 sm:mx-6 mt-5 rounded-xl border border-slate-200 p-4 text-xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Tanggal & Hari</span>
                    <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Kelas</span>
                    <span class="font-bold text-blue-700">{{ $class->name }}</span>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-200">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Mata Pelajaran</span>
                    <span class="font-bold text-slate-900">{{ $schedule->subject?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Jam Pelajaran</span>
                    <span class="font-bold text-slate-900">{{ $schedule->period_label }} ({{ $schedule->time_range }} WIB)</span>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-200">
                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Guru Pengajar</span>
                <span class="font-bold text-slate-900">{{ $schedule->teacher?->name ?? '-' }}
                    @if($schedule->teacher?->nip)
                        <span class="text-slate-500 font-normal">(NIP: {{ $schedule->teacher->nip }})</span>
                    @endif
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin_kelas.report.store', $schedule->id) }}" class="p-5 sm:p-6 space-y-5">
            @csrf

            {{-- Status Kehadiran Guru --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Kehadiran Guru Pengajar</label>
                <select name="teacher_status" id="teacherStatusSelect" onchange="toggleTaskSection()" required
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="hadir" {{ old('teacher_status', $existingReport?->teacher_status) === 'hadir' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                    <option value="terlambat" {{ old('teacher_status', $existingReport?->teacher_status) === 'terlambat' ? 'selected' : '' }}>Hadir Terlambat</option>
                    <option value="tidak_hadir" {{ old('teacher_status', $existingReport?->teacher_status) === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir (Izin / Sakit / Alpa)</option>
                </select>
            </div>

            {{-- Waktu Kedatangan --}}
            <div id="arrivalTimeContainer">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Kedatangan Guru (WIB)</label>
                <input type="time" name="teacher_arrival_time"
                    value="{{ old('teacher_arrival_time', $existingReport?->teacher_arrival_time ? substr($existingReport->teacher_arrival_time, 0, 5) : date('H:i')) }}"
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            {{-- Bagian Tugas (Jika Guru Tidak Hadir) --}}
            <div id="taskSection" class="p-4 bg-indigo-50/60 rounded-xl border border-indigo-200 space-y-3 hidden">
                <label class="flex items-center gap-2 text-xs font-bold text-indigo-900 cursor-pointer">
                    <input type="checkbox" name="has_task" value="1" id="hasTaskCheck"
                        {{ old('has_task', $existingReport?->has_task) ? 'checked' : '' }}
                        class="w-3.5 h-3.5 rounded text-indigo-600 focus:ring-indigo-500">
                    <span>Guru Memberikan Tugas Pembelajaran untuk Siswa</span>
                </label>
                <div>
                    <label class="block text-[11px] font-semibold text-indigo-800 mb-1">Keterangan / Rincian Tugas</label>
                    <textarea name="task_description" rows="2"
                        placeholder="Contoh: Mengerjakan latihan hal. 25-30 di buku catatan dan dikumpulkan."
                        class="block w-full rounded-lg border border-indigo-300 p-2.5 text-xs focus:border-indigo-600 focus:outline-none resize-none">{{ old('task_description', $existingReport?->task_description) }}</textarea>
                </div>
            </div>

            {{-- Catatan Siswa Tidak Hadir --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Siswa Tidak Hadir pada Jam Ini <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="text" name="absent_students_notes"
                    value="{{ old('absent_students_notes', $existingReport?->absent_students_notes) }}"
                    placeholder="Contoh: Dedi Kurniawan (Sakit), Citra Lestari (Izin UKS)"
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            {{-- Catatan Kondisi Kelas --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Kondisi Kelas <span class="text-rose-500">*</span></label>
                <textarea name="class_condition_notes" rows="4" required
                    placeholder="Contoh: KBM berlangsung tertib, proyektor berfungsi baik, 2 siswa izin ke UKS."
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none resize-none">{{ old('class_condition_notes', $existingReport?->class_condition_notes ?? 'Kondisi kelas tertib dan KBM berjalan lancar.') }}</textarea>
            </div>

            {{-- Info Pending --}}
            <div class="flex items-start gap-2.5 p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                <span>Laporan ini akan berstatus <strong>Pending</strong> sampai ditinjau dan disetujui oleh Admin Kurikulum.</span>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-4 border-t border-slate-100">
                <a href="{{ route('admin_kelas.index') }}"
                    class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-xmark"></i> Batal
                </a>
                <button type="submit"
                    class="w-full sm:w-auto justify-center px-6 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Laporan ke Kurikulum
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleTaskSection() {
    const status = document.getElementById('teacherStatusSelect').value;
    const taskSection = document.getElementById('taskSection');
    const arrivalContainer = document.getElementById('arrivalTimeContainer');

    if (status === 'tidak_hadir') {
        taskSection.classList.remove('hidden');
        arrivalContainer.classList.add('hidden');
    } else {
        taskSection.classList.add('hidden');
        arrivalContainer.classList.remove('hidden');
    }
}

// Jalankan saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    toggleTaskSection();
});
</script>
@endpush
@endsection
