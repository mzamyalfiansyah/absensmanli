@extends('layouts.app')

@section('title', 'Surat Panggilan Orang Tua')
@section('page_title', 'Surat Panggilan Orang Tua')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Surat Panggilan Orang Tua / Wali</h2>
            <p class="text-xs text-slate-500 mt-0.5">Format resmi surat dinas sekolah siap cetak untuk pemanggilan konsultasi pelanggaran siswa.</p>
        </div>
        <a href="{{ route('kedisiplinan.summons.create') }}"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-plus mr-1.5"></i> Buat Surat Panggilan Baru
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Surat</th>
                        <th class="py-3 px-4">Siswa & Kelas</th>
                        <th class="py-3 px-4">Jadwal Pertemuan</th>
                        <th class="py-3 px-4">Tempat</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($summons as $sm)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $sm->letter_number }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $sm->student->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $sm->student->nis ?? '-' }} &bull; {{ $sm->student->schoolClass->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $sm->meeting_date->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-500">{{ substr($sm->meeting_time, 0, 5) }} WIB</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600">{{ $sm->meeting_place }}</td>
                            <td class="py-3 px-4">
                                {!! $sm->status_badge !!}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('kedisiplinan.summons.print', $sm->id) }}" target="_blank"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-bold text-[11px] transition shadow-xs">
                                    <i class="fa-solid fa-print mr-1.5"></i> Cetak Surat
                                </a>
                                <button type="button" onclick="openStatusModal({{ $sm->id }}, '{{ $sm->status }}')"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-[11px] transition">
                                    Status
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">Belum ada surat panggilan orang tua yang diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $summons->links() }}
        </div>
    </div>
</div>

<!-- Modal Update Status Surat Panggilan -->
<div id="statusModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Ubah Status Kehadiran Orang Tua</h3>
            <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="statusForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700">Status Pertemuan</label>
                <select name="status" id="statusSelect" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="draft">Draft</option>
                    <option value="dikirim">Surat Dikirim</option>
                    <option value="hadir">Orang Tua Hadir</option>
                    <option value="tidak_hadir">Orang Tua Tidak Hadir</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Catatan Hasil Pertemuan (Opsional)</label>
                <textarea name="notes" rows="2" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(id, currentStatus) {
    document.getElementById('statusForm').action = '/kedisiplinan/summons/' + id + '/status';
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>
@endsection
