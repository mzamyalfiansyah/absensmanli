@extends('layouts.app')

@section('title', 'Sesi Absensi QR Aktif')
@section('page_title', 'Sesi QR: ' . ($session->schedule->subject->name ?? 'Mata Pelajaran'))

@section('content')
<div class="space-y-6">
    <!-- Header Banner Sesi -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-900 rounded-2xl p-4 sm:p-6 text-white shadow-lg shadow-blue-900/10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $session->isOpen() ? 'bg-emerald-400 text-emerald-950 animate-pulse' : 'bg-rose-400 text-rose-950' }} uppercase tracking-wider">
                    {{ $session->isOpen() ? '● Sesi Aktif' : 'Sesi ' . ucfirst($session->status) }}
                </span>
                <span class="text-xs text-blue-200 font-medium">Kelas {{ $class->name }} &bull; {{ $session->schedule->period_label }}</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-1.5">{{ $session->title }}</h2>
            <p class="text-xs text-blue-100/90 mt-1">
                Dibuka: <strong>{{ $session->opened_at->format('H:i') }} WIB</strong> &bull; Berakhir: <strong>{{ $session->expires_at->format('H:i') }} WIB</strong> 
                <span class="text-blue-200">({{ $session->expires_at->diffForHumans() }})</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            @if($session->isOpen())
                <form method="POST" action="{{ route('guru.sessions.close', $session->id) }}" class="flex-1 sm:flex-initial">
                    @csrf
                    <button type="submit" onclick="return confirm('Tutup sesi absensi ini sekarang? Siswa tidak akan dapat memindai lagi.')"
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition flex items-center justify-center shadow-xs">
                        <i class="fa-solid fa-lock mr-2"></i> Tutup Sesi
                    </button>
                </form>
            @endif
            <button type="button" onclick="window.location.reload()"
                class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-blue-700 hover:bg-blue-50 transition flex items-center justify-center shadow-xs">
                <i class="fa-solid fa-arrows-rotate mr-2"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Main Grid: QR Display and Student List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- QR Code Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs flex flex-col items-center justify-center text-center space-y-4">
            <div class="flex items-center justify-between w-full">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">QR Code Absensi</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                    <i class="fa-solid fa-rotate mr-1"></i> Auto-Sync
                </span>
            </div>
            
            <div class="p-3 bg-white border-2 border-slate-200 rounded-2xl shadow-inner max-w-[280px] sm:max-w-xs w-full aspect-square flex items-center justify-center relative group">
                @if($session->isOpen())
                    <img id="qrImage" src="{{ $qrCodeDataUri }}" alt="QR Code Absensi Siswa" class="w-full h-full object-contain select-none">
                @else
                    <div class="text-center p-6 text-slate-400">
                        <i class="fa-solid fa-lock text-4xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-semibold">Sesi telah ditutup atau kedaluwarsa.</p>
                    </div>
                @endif
            </div>

            <!-- Fullscreen / Proyektor Button -->
            @if($session->isOpen())
                <button type="button" onclick="openQrModal()"
                    class="w-full py-2.5 px-4 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition flex items-center justify-center border border-blue-200">
                    <i class="fa-solid fa-expand mr-2"></i> Perbesar / Mode Layar Penuh
                </button>
            @endif

            <div class="space-y-1.5 w-full text-center">
                <div class="text-[11px] text-slate-400">Kode Token Sesi:</div>
                <div class="flex items-center justify-center space-x-2">
                    <div id="sessionTokenText" class="font-mono text-xs font-bold text-slate-700 bg-slate-100 py-1.5 px-3 rounded-lg truncate max-w-[200px] select-all">
                        {{ $session->token }}
                    </div>
                    <button type="button" onclick="copyToken()" title="Salin Kode Token"
                        class="p-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs transition">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
                <span id="copyToast" class="hidden text-[10px] text-emerald-600 font-bold">✓ Token berhasil disalin!</span>
            </div>

            <div class="text-[11px] text-slate-500 flex items-center justify-center pt-1 border-t border-slate-100 w-full">
                <i class="fa-solid fa-shield-halved text-emerald-500 mr-1.5"></i>
                Geofencing radius GPS sekolah aktif
            </div>

            <!-- Auto-refresh countdown indicator -->
            <div class="text-[11px] text-slate-400 flex items-center justify-center space-x-1 pt-1">
                <i class="fa-regular fa-clock text-[10px]"></i>
                <span>Pembaruan otomatis: <strong id="refreshCountdown" class="text-slate-600">10</strong>s</span>
            </div>
        </div>

        <!-- Student Attendance Realtime Roster -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Siswa yang Sudah Absen -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center">
                            <i class="fa-solid fa-circle-check text-emerald-500 mr-2"></i> Siswa Sudah Absen
                        </h3>
                        <p class="text-xs text-slate-500">Terekam dengan verifikasi server & timestamp resmi.</p>
                    </div>
                    <span class="self-start sm:self-auto px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                        {{ count($scannedList) }} Siswa Hadir
                    </span>
                </div>

                <div class="overflow-x-auto -mx-5 sm:mx-0">
                    <table class="w-full text-left text-xs min-w-[500px]">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100 pb-2">
                                <th class="py-2.5 px-4 sm:px-0">Waktu</th>
                                <th class="py-2.5 px-2">NIS</th>
                                <th class="py-2.5 px-2">Nama Siswa</th>
                                <th class="py-2.5 px-2">Status</th>
                                <th class="py-2.5 px-4 sm:px-0">Validasi Lokasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($scannedList as $item)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-2.5 px-4 sm:px-0 text-slate-500 whitespace-nowrap font-mono">
                                        {{ $item['attendance']->scanned_at ? $item['attendance']->scanned_at->format('H:i:s') : '-' }}
                                    </td>
                                    <td class="py-2.5 px-2 font-mono text-slate-600">{{ $item['student']->nis }}</td>
                                    <td class="py-2.5 px-2 font-bold text-slate-800">{{ $item['student']->name }}</td>
                                    <td class="py-2.5 px-2">{!! $item['attendance']->status_badge !!}</td>
                                    <td class="py-2.5 px-4 sm:px-0">
                                        {!! $item['attendance']->location_status_badge !!}
                                        @if($item['attendance']->distance_meters !== null)
                                            <span class="text-[11px] text-slate-400 font-mono">({{ $item['attendance']->distance_meters }}m)</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        <i class="fa-solid fa-qrcode text-3xl mb-2 text-slate-300 block"></i>
                                        Belum ada siswa yang memindai QR Code sesi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Siswa Belum Absen & Input Manual Guru -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center">
                            <i class="fa-solid fa-user-clock text-amber-500 mr-2"></i> Belum Absen / Presensi Manual
                        </h3>
                        <p class="text-xs text-slate-500">Beri status kehadiran untuk siswa berkendala perangkat atau surat izin.</p>
                    </div>
                    <span class="self-start sm:self-auto px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800">
                        {{ count($unscannedList) }} Siswa Belum
                    </span>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[500px]">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100 pb-2">
                                <th class="py-2">NIS</th>
                                <th class="py-2">Nama Siswa</th>
                                <th class="py-2 text-right">Tindakan Guru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($unscannedList as $un)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-2.5 font-mono text-slate-600">{{ $un->nis }}</td>
                                    <td class="py-2.5 font-bold text-slate-800">{{ $un->name }}</td>
                                    <td class="py-2.5 text-right">
                                        <div class="inline-flex items-center space-x-1.5">
                                            <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="student_id" value="{{ $un->id }}">
                                                <input type="hidden" name="status" value="hadir">
                                                <button type="submit" title="Tandai Hadir Manual" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs transition border border-emerald-200">
                                                    ✓ Hadir
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="student_id" value="{{ $un->id }}">
                                                <input type="hidden" name="status" value="sakit">
                                                <button type="submit" title="Tandai Sakit" class="px-2.5 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-xs transition border border-purple-200">
                                                    + Sakit
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="student_id" value="{{ $un->id }}">
                                                <input type="hidden" name="status" value="izin">
                                                <button type="submit" title="Tandai Izin" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs transition border border-blue-200">
                                                    i Izin
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="student_id" value="{{ $un->id }}">
                                                <input type="hidden" name="status" value="alpa">
                                                <button type="submit" title="Tandai Alpa" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs transition border border-rose-200">
                                                    ✕ Alpa
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">Seluruh siswa di kelas ini telah mencatat absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-3">
                    @forelse($unscannedList as $un)
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-xs">{{ $un->name }}</span>
                                <span class="font-mono text-[11px] text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $un->nis }}</span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5 pt-1">
                                <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $un->id }}">
                                    <input type="hidden" name="status" value="hadir">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px] shadow-xs active:scale-95 transition">
                                        Hadir
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $un->id }}">
                                    <input type="hidden" name="status" value="sakit">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-purple-600 text-white font-bold text-[11px] shadow-xs active:scale-95 transition">
                                        Sakit
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $un->id }}">
                                    <input type="hidden" name="status" value="izin">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-blue-600 text-white font-bold text-[11px] shadow-xs active:scale-95 transition">
                                        Izin
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('guru.sessions.manual', $session->id) }}" class="w-full">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $un->id }}">
                                    <input type="hidden" name="status" value="alpa">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-rose-600 text-white font-bold text-[11px] shadow-xs active:scale-95 transition">
                                        Alpa
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-slate-400">Seluruh siswa di kelas ini telah mencatat absensi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen / Proyektor Modal -->
<div id="qrModal" class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md hidden flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-6 text-center shadow-2xl relative space-y-4">
        <button type="button" onclick="closeQrModal()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-800 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 uppercase tracking-wider">
                Mode Proyektor / Layar Penuh
            </span>
            <h3 class="text-lg font-black text-slate-900 mt-1">{{ $session->title }}</h3>
            <p class="text-xs text-slate-500">Kelas {{ $class->name }} &bull; SMAN 5 Karawang</p>
        </div>

        <div class="p-4 bg-white border-4 border-slate-100 rounded-2xl shadow-inner max-w-sm mx-auto aspect-square flex items-center justify-center">
            <img src="{{ $qrCodeDataUri }}" alt="QR Code Absensi Siswa" class="w-full h-full object-contain">
        </div>

        <div class="space-y-1">
            <div class="text-[11px] text-slate-400">Kode Token Absensi:</div>
            <div class="font-mono text-xs font-bold text-slate-800 bg-slate-100 py-1.5 px-3 rounded-lg select-all">
                {{ $session->token }}
            </div>
        </div>

        <button type="button" onclick="closeQrModal()" class="w-full py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
            Tutup Tampilan Penuh
        </button>
    </div>
</div>

@push('scripts')
<script>
function openQrModal() {
    document.getElementById('qrModal').classList.remove('hidden');
}

function closeQrModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

function copyToken() {
    const text = document.getElementById('sessionTokenText').innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.getElementById('copyToast');
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    });
}

// Auto-refresh timer (every 10 seconds for active session)
@if($session->isOpen())
let countdown = 10;
const countdownEl = document.getElementById('refreshCountdown');
setInterval(() => {
    countdown--;
    if (countdownEl) countdownEl.innerText = countdown;
    if (countdown <= 0) {
        window.location.reload();
    }
}, 1000);
@endif
</script>
@endpush
@endsection
