@extends('layouts.app')

@section('title', 'Dashboard Guru')
@section('page_title', 'Dashboard Guru Pengajar')

@section('content')
<div class="space-y-6">
    @if(!$teacher)
    <div class="p-6 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2 text-amber-600"></i>
        Akun Anda belum terhubung dengan data Guru di sistem. Hubungi Administrator.
    </div>
    @else
    <!-- Header Info Guru -->
    <div
        class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-6 text-white shadow-lg shadow-blue-900/10 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <span
                class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white uppercase tracking-wider">Portal
                Guru Pengajar</span>
            <h2 class="text-2xl font-black tracking-tight mt-1">{{ $teacher->name }}</h2>
            <p class="text-xs text-blue-100 mt-1">
                NIP: <strong>{{ $teacher->nip }}</strong> &bull; Bidang Studi:
                <strong>{{ $teacher->specialization ?? 'Guru Mata Pelajaran' }}</strong>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('guru.schedules') }}"
                class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-calendar-days mr-1.5"></i> Jadwal Mengajar Mingguan
            </a>
            <a href="{{ route('guru.history') }}"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center border border-blue-400">
                <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Riwayat Kehadiran
            </a>
        </div>
    </div>

    <!-- Jadwal Mengajar Hari Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-calendar-check mr-2 text-blue-600"></i> Jadwal Mengajar Hari Ini
                    ({{ ucfirst($currentDay) }})
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Buka sesi absensi dengan QR Code dinamis dan lakukan verifikasi
                    kehadiran mengajar Anda.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($todaySchedules as $sch)
            @php
            $session = $activeSessions[$sch->id] ?? null;
            $myAttendance = $myAttendancesToday[$sch->id] ?? null;
            @endphp
            <div class="py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="space-y-1">
                    <div class="flex items-center space-x-2">
                        <span
                            class="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-bold">{{ $sch->period_label }}</span>
                        <span class="text-xs font-medium text-slate-500">{{ $sch->time_range }}</span>
                        <span class="text-sm font-bold text-slate-900">{{ $sch->subject->name ?? '-' }}</span>
                    </div>
                    <div class="text-xs text-slate-600">
                        Kelas: <strong>{{ $sch->schoolClass->name ?? '-' }}</strong>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                        <span class="text-slate-500">Kehadiran Anda:</span>
                        @if($myAttendance)
                        {!! $myAttendance->status_badge !!}
                        <span
                            class="text-slate-500">({{ $myAttendance->check_in_time ? substr($myAttendance->check_in_time, 0, 5) : 'Tercatat' }})</span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">Belum
                            Verifikasi</span>
                        @endif

                        @if($session)
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-500">Sesi QR:</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $session->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($session->status) }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                    <!-- Verifikasi Kehadiran Guru Button / Modal -->
                    <button type="button"
                        onclick="openCheckInModal('{{ $sch->id }}', '{{ $sch->subject->name }}', '{{ $sch->schoolClass->name }}')"
                        class="w-full sm:w-auto px-3.5 py-2 rounded-xl text-xs font-bold border border-slate-300 text-slate-700 hover:bg-slate-50 transition flex items-center justify-center">
                        <i class="fa-solid fa-user-check mr-1.5 text-emerald-600"></i> Verifikasi Kehadiran Guru
                    </button>

                    <!-- Buka atau Lihat Sesi QR -->
                    @if($session && $session->isOpen())
                    <a href="{{ route('guru.sessions.show', $session->id) }}"
                        class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center justify-center shadow-xs">
                        <i class="fa-solid fa-qrcode mr-1.5"></i> Tampilkan QR Code Aktif
                    </a>
                    @else
                    <form method="POST" action="{{ route('guru.sessions.open', $sch->id) }}"
                        class="w-full sm:w-auto inline">
                        @csrf
                        <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition flex items-center justify-center shadow-xs">
                            <i class="fa-solid fa-plus mr-1.5"></i> Buka Sesi QR Absensi
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-xs text-slate-400">Tidak ada jadwal mengajar untuk Anda hari ini.</div>
            @endforelse
        </div>
    </div>

    <!-- Modal Verifikasi Kehadiran Guru -->
    <div id="checkInModal"
        class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900" id="modalTitle">Verifikasi Kehadiran Guru</h3>
                <button type="button" onclick="closeCheckInModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="checkInForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Status Kehadiran Mengajar</label>
                    <select name="status" id="teacherStatusSelect" onchange="toggleTaskField()"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                        <option value="hadir">Hadir di Kelas Tepat Waktu</option>
                        <option value="terlambat">Hadir Terlambat</option>
                        <option value="tidak_hadir">Tidak Hadir / Izin / Sakit</option>
                    </select>
                </div>

                <div id="taskFieldContainer"
                    class="hidden space-y-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="has_task" value="1" id="hasTaskCheckbox"
                            class="rounded text-blue-600">
                        <span>Berikan Tugas / Bahan Pembelajaran untuk Siswa</span>
                    </label>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600">Keterangan / Instruksi Tugas</label>
                        <textarea name="task_description" rows="2"
                            placeholder="Contoh: Kerjakan LKS Matematika Hal 45-48 No 1-10 dikumpulkan di meja guru."
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Catatan kegiatan KBM..."
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" onclick="closeCheckInModal()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan
                        Verifikasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openCheckInModal(scheduleId, subjectName, className) {
        document.getElementById('modalTitle').innerText = 'Verifikasi Kehadiran: ' + subjectName + ' (' + className +
            ')';
        document.getElementById('checkInForm').action = '/guru/schedules/' + scheduleId + '/check-in';
        document.getElementById('checkInModal').classList.remove('hidden');
    }

    function closeCheckInModal() {
        document.getElementById('checkInModal').classList.add('hidden');
    }

    function toggleTaskField() {
        const status = document.getElementById('teacherStatusSelect').value;
        const container = document.getElementById('taskFieldContainer');
        if (status === 'tidak_hadir') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
    </script>
    @endif
</div>
@endsection