@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')
@section('page_title', 'Kelola Jadwal Pelajaran (KBM)')

@section('content')
@php
$r = $scheduleRoutes ?? [
    'index'   => 'admin.master.schedules',
    'store'   => 'admin.schedules.store',
    'update'  => 'admin.schedules.update',
    'destroy' => 'admin.schedules.destroy',
];
@endphp
<div class="space-y-6">


    {{-- ERROR VALIDASI --}}
    @if($errors->any())
    <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Jadwal Pelajaran SMAN 5 Karawang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Jadwal KBM per kelas terhubung langsung dengan sesi QR Code guru dan rekonsiliasi Admin Kelas.</p>
        </div>
        <button type="button" onclick="openScheduleModal()"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center">
            <i class="fa-solid fa-calendar-plus mr-1.5"></i> Buat Jadwal Baru
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route($r['index']) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Hari</label>
                <select name="day" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Hari</option>
                    @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $d)
                        <option value="{{ $d }}" {{ request('day') === $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Kelas</label>
                <select name="class_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Pilih Guru Pengajar</label>
                <select name="teacher_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route($r['index']) }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
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
                        <th class="py-3 px-4">Hari</th>
                        <th class="py-3 px-4">Jam Ke-</th>
                        <th class="py-3 px-4">Waktu (WIB)</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Guru Pengajar</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $sch)
                        @php
                        $schedulePayload = [
                            'day_of_week'  => $sch->day_of_week,
                            'class_id'     => $sch->class_id,
                            'subject_id'   => $sch->subject_id,
                            'teacher_id'   => $sch->teacher_id,
                            'period_start' => $sch->period_start,
                            'period_end'   => $sch->period_end,
                            'start_time'   => substr((string) $sch->start_time, 0, 5),
                            'end_time'     => substr((string) $sch->end_time, 0, 5),
                        ];
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 capitalize">{{ $sch->day_of_week }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold">
                                    {{ $sch->period_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-mono">{{ $sch->time_range }}</td>
                            <td class="py-3 px-4 font-bold text-indigo-700">{{ $sch->schoolClass->name ?? '-' }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $sch->subject->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $sch->teacher->name ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                        onclick="openScheduleModal(this)"
                                        data-url="{{ route($r['update'], $sch->id) }}"
                                        data-schedule="{{ json_encode($schedulePayload) }}"
                                        class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold transition">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                    </button>

                                    <form method="POST" action="{{ route($r['destroy'], $sch->id) }}"
                                        onsubmit="return confirm('Yakin mau hapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-bold transition">
                                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Tidak ada jadwal pelajaran yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $schedules->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Jadwal -->
<div id="scheduleModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="scheduleModalTitle" class="text-base font-bold text-slate-900">Buat Jadwal Pelajaran Baru</h3>
            <button type="button" onclick="closeScheduleModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="scheduleForm" method="POST" action="{{ route($r['store']) }}" class="space-y-3.5">
            @csrf
            <input type="hidden" name="_method" id="scheduleMethod" value="POST">
            <input type="hidden" name="academic_year_id" value="{{ $activeYear->id ?? 1 }}">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hari</label>
                    <select name="day_of_week" id="f_day_of_week" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        <option value="senin">Senin</option>
                        <option value="selasa">Selasa</option>
                        <option value="rabu">Rabu</option>
                        <option value="kamis">Kamis</option>
                        <option value="jumat">Jumat</option>
                        <option value="sabtu">Sabtu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Kelas</label>
                    <select name="class_id" id="f_class_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Mata Pelajaran</label>
                <select name="subject_id" id="f_subject_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    @foreach($subjects as $sb)
                        <option value="{{ $sb->id }}">{{ $sb->name }} ({{ $sb->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Guru Pengajar</label>
                <select name="teacher_id" id="f_teacher_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->specialization ?? 'Guru' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Jam Ke- Mulai</label>
                    <input type="number" name="period_start" id="f_period_start" value="1" min="1" max="12" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Jam Ke- Selesai</label>
                    <input type="number" name="period_end" id="f_period_end" value="2" min="1" max="12" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Waktu Mulai (WIB)</label>
                    <input type="time" name="start_time" id="f_start_time" value="07:15" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Waktu Selesai (WIB)</label>
                    <input type="time" name="end_time" id="f_end_time" value="08:45" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<script>
const scheduleModal  = document.getElementById('scheduleModal');
const scheduleForm   = document.getElementById('scheduleForm');
const scheduleFields = ['day_of_week','class_id','subject_id','teacher_id','period_start','period_end','start_time','end_time'];
const scheduleDefaults = { period_start: 1, period_end: 2, start_time: '07:15', end_time: '08:45' };

function openScheduleModal(btn = null) {
    if (btn) {
        const data = JSON.parse(btn.dataset.schedule);
        scheduleForm.action = btn.dataset.url;
        document.getElementById('scheduleMethod').value = 'PUT';
        document.getElementById('scheduleModalTitle').textContent = 'Edit Jadwal Pelajaran';
        scheduleFields.forEach(f => {
            document.getElementById('f_' + f).value = data[f] ?? '';
        });
    } else {
        scheduleForm.action = "{{ route($r['store']) }}";
        document.getElementById('scheduleMethod').value = 'POST';
        document.getElementById('scheduleModalTitle').textContent = 'Buat Jadwal Pelajaran Baru';
        scheduleFields.forEach(f => {
            if (scheduleDefaults[f] !== undefined) {
                document.getElementById('f_' + f).value = scheduleDefaults[f];
            }
        });
    }

    scheduleModal.classList.remove('hidden');
    scheduleModal.classList.add('flex');
}

function closeScheduleModal() {
    scheduleModal.classList.add('hidden');
    scheduleModal.classList.remove('flex');
}

scheduleModal.addEventListener('click', function (e) {
    if (e.target === scheduleModal) closeScheduleModal();
});
</script>
@endsection