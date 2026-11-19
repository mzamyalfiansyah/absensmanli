@extends('layouts.app')

@section('title', 'Verifikasi Laporan Kelas')
@section('page_title', 'Verifikasi Laporan Kondisi Kelas (Admin Kelas)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Verifikasi & Rekonsiliasi Laporan Admin Kelas</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Data laporan kehadiran guru dari Admin Kelas tidak langsung terverifikasi sebelum disetujui oleh Kurikulum.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-xs">
                <i class="fa-solid fa-clipboard-check mr-1"></i> Alur Rekonsiliasi
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('kurikulum.class_reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Status Laporan</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Kelas</label>
                <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('kurikulum.class_reports.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
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
                        <th class="py-3 px-4">Tanggal & Jam</th>
                        <th class="py-3 px-4">Kelas & Pelapor</th>
                        <th class="py-3 px-4">Guru & Pelajaran</th>
                        <th class="py-3 px-4">Kehadiran Guru</th>
                        <th class="py-3 px-4">Tugas & Kondisi Kelas</th>
                        <th class="py-3 px-4">Status Verifikasi</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $rep)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $rep->date->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-500">{{ $rep->period_label }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-blue-700">{{ $rep->schoolClass->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">Oleh: {{ $rep->submittedByUser->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $rep->teacher->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $rep->schedule->subject->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold capitalize {{ $rep->teacher_status === 'hadir' ? 'text-emerald-700' : ($rep->teacher_status === 'terlambat' ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ ucfirst($rep->teacher_status) }}
                                </span>
                                @if($rep->teacher_arrival_time)
                                    <div class="text-[11px] text-slate-500">Pukul {{ substr($rep->teacher_arrival_time, 0, 5) }} WIB</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                @if($rep->has_task)
                                    <div class="text-indigo-600 font-semibold mb-1">
                                        <i class="fa-solid fa-file-check mr-1"></i> Ada Tugas: {{ $rep->task_description }}
                                    </div>
                                @endif
                                <div class="text-slate-700 line-clamp-2">{{ $rep->class_condition_notes ?? 'Kondisi kelas kondusif.' }}</div>
                                @if($rep->absent_students_notes)
                                    <div class="text-[11px] text-slate-500 mt-0.5">Siswa Tidak Hadir: {{ $rep->absent_students_notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                {!! $rep->status_badge !!}
                                @if($rep->reviewedByUser)
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Oleh: {{ $rep->reviewedByUser->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                @if($rep->status === 'pending')
                                    <form method="POST" action="{{ route('kurikulum.class_reports.approve', $rep->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Verifikasi dan setujui laporan ini? Kehadiran guru akan direkonsiliasi.')"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-xs">
                                            <i class="fa-solid fa-check mr-1"></i> Setujui
                                        </button>
                                    </form>

                                    <button type="button" onclick="openRejectModal({{ $rep->id }})"
                                        class="px-2.5 py-1.5 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold text-[11px] transition">
                                        Tolak
                                    </button>
                                @else
                                    <span class="text-slate-400 text-xs italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Tidak ada laporan kelas yang sesuai kriteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $reports->links() }}
        </div>
    </div>
</div>

<!-- Modal Tolak Laporan -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Tolak Laporan Kondisi Kelas</h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="rejectForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Alasan Penolakan</label>
                <textarea name="review_notes" rows="3" required placeholder="Jelaskan alasan penolakan laporan..."
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white">Tolak Laporan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = '/kurikulum/class-reports/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
