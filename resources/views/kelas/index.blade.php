@extends('layouts.app')

@section('title', 'Laporan Kelas')
@section('page_title', 'Laporan Kondisi Kelas & Kehadiran Guru')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-600"></i>
                Laporan Kelas {{ $class->name }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Catat kehadiran guru di setiap jam pelajaran dan laporkan siswa yang berhalangan hadir.</p>
        </div>
        <div class="flex-shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                <i class="fa-solid fa-user-tie w-3.5 text-center"></i>
                {{ auth()->user()->name }}
            </span>
        </div>
    </div>

    {{-- Jadwal & Form Laporan --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-calendar-day text-indigo-600"></i>
                Jadwal Pelajaran Hari Ini ({{ ucfirst($currentDay) }})
            </h3>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($schedules as $sch)
                @php $rep = $todayReports[$sch->id] ?? null; @endphp
                <div class="p-5 sm:p-6 hover:bg-slate-50/50 transition">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        {{-- Info Jadwal --}}
                        <div class="flex-1 min-w-0 space-y-2">
                            {{-- Baris pertama: jam & mapel --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">{{ $sch->period_label }}</span>
                                <span class="text-xs font-mono text-slate-500 bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-200">{{ $sch->time_range }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sch->subject?->name ?? '-' }}</span>
                            </div>

                            {{-- Guru --}}
                            <div class="text-xs text-slate-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-tie text-slate-400 w-4 text-center"></i>
                                <span>Guru: <strong>{{ $sch->teacher?->name ?? '-' }}</strong>
                                    @if($sch->teacher?->nip)
                                        <span class="text-slate-400">(NIP: {{ $sch->teacher->nip }})</span>
                                    @endif
                                </span>
                            </div>

                            {{-- Status laporan --}}
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="text-slate-500">Status Laporan:</span>
                                @if($rep)
                                    {!! $rep->status_badge !!}
                                    <span class="text-slate-700">Guru: <strong>{{ ucfirst($rep->teacher_status) }}</strong></span>
                                    @if($rep->teacher_arrival_time)
                                        <span class="text-slate-500">({{ substr($rep->teacher_arrival_time, 0, 5) }})</span>
                                    @endif
                                    @if($rep->has_task)
                                        <span class="font-semibold text-indigo-700">
                                            <i class="fa-solid fa-check mr-1"></i>Ada Tugas: {{ $rep->task_description }}
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Belum Dilaporkan</span>
                                @endif
                            </div>

                            {{-- Daftar siswa tidak hadir --}}
                            @php $abs = $todayAbsences[$sch->id] ?? collect(); @endphp
                            @if($abs->isNotEmpty())
                                <div class="pt-1 space-y-1.5 border-t border-slate-100 mt-2">
                                    <span class="text-xs text-slate-500 font-medium">Siswa tidak hadir pada jam ini:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($abs as $a)
                                            <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-rose-50 border border-rose-100 rounded-lg text-xs">
                                                <span class="font-semibold text-slate-800">{{ $a->student?->name }}</span>
                                                {!! $a->status_badge !!}
                                                @if($a->attachment)
                                                    <a href="{{ route('attendance.attachment', $a->id) }}" target="_blank" class="text-blue-600 hover:text-blue-700 font-semibold">
                                                        <i class="fa-solid fa-paperclip"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex flex-wrap sm:flex-nowrap gap-2 lg:flex-col xl:flex-row flex-shrink-0">
                            {{-- Tombol Input Siswa Tidak Hadir --}}
                            <button type="button"
                                onclick="openAbsentStudentModal('{{ $sch->id }}', '{{ $sch->subject?->name }}')"
                                class="flex-1 sm:flex-none justify-center px-3 py-2.5 rounded-xl text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fa-solid fa-user-xmark text-rose-500"></i>
                                <span>Siswa Tidak Hadir</span>
                            </button>

                            {{-- Tombol Lapor Kondisi Kelas & Guru --}}
                            <a href="{{ route('admin_kelas.report.create', $sch->id) }}"
                                class="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs whitespace-nowrap
                                {{ $rep ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/20' }}">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>{{ $rep ? 'Edit Laporan' : 'Input Laporan Guru' }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-14 text-center">
                    <div class="text-slate-300 text-4xl mb-3"><i class="fa-solid fa-calendar-xmark"></i></div>
                    <div class="text-sm font-semibold text-slate-500">Tidak ada jadwal KBM kelas hari ini</div>
                    <div class="text-xs text-slate-400 mt-1">Hari {{ ucfirst($currentDay) }} tidak ada pelajaran yang terjadwal.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Input Siswa Tidak Hadir --}}
<div id="absentStudentModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <h3 class="text-sm font-bold text-slate-900" id="absentModalTitle">Catat Siswa Tidak Hadir</h3>
            <button type="button" onclick="closeAbsentStudentModal()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <form id="absentStudentForm" method="POST" action="" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Siswa</label>
                <select name="student_id" required class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Pilih Siswa...</option>
                    @foreach($students as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->nis }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Ketidakhadiran</label>
                <select name="status" id="absentStatus" onchange="toggleSuratField()" required class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="sakit">Sakit (Ada Surat Dokter)</option>
                    <option value="izin">Izin (Ada Surat Orang Tua)</option>
                    <option value="alpa">Alpa (Tanpa Keterangan)</option>
                </select>
            </div>

            <div id="suratField" class="hidden">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Surat Dokter (JPG/PNG/PDF, maks 2 MB)</label>
                <input type="file" name="attachment" id="suratInput" accept=".jpg,.jpeg,.png,.pdf"
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                <input type="text" name="notes" placeholder="Contoh: Surat dititipkan teman sekelas..."
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAbsentStudentModal()"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-sm shadow-rose-600/20 transition">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleSuratField() {
    const sakit = document.getElementById('absentStatus').value === 'sakit';
    document.getElementById('suratField').classList.toggle('hidden', !sakit);
    document.getElementById('suratInput').required = sakit;
}

function openAbsentStudentModal(scheduleId, subjectName) {
    document.getElementById('absentModalTitle').innerText = 'Siswa Tidak Hadir: ' + (subjectName || '');
    document.getElementById('absentStudentForm').action = '/kelas/absent-student/' + scheduleId;
    document.getElementById('absentStudentModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    toggleSuratField();
}

function closeAbsentStudentModal() {
    document.getElementById('absentStudentModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Tutup modal saat klik backdrop
document.getElementById('absentStudentModal').addEventListener('click', function(e) {
    if (e.target === this) closeAbsentStudentModal();
});
</script>
@endpush
@endsection
