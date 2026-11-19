@extends('layouts.app')

@section('title', 'Jadwal Mengajar Mingguan')
@section('page_title', 'Jadwal Mengajar Mingguan')

@section('content')
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

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center
                sm:justify-between gap-4 bg-white p-5 rounded-2xl
                border border-slate-200 shadow-xs">

        <div>
            <h2 class="text-base font-bold text-slate-900">
                @if($isSuperAdmin)
                Jadwal Mengajar Seluruh Guru
                @else
                Jadwal Mengajar: {{ $teacher->name }}
                @endif
            </h2>

            <p class="text-xs text-slate-500 mt-1">
                @if($isSuperAdmin)
                Kelola dan pantau jadwal seluruh guru,
                mata pelajaran, dan kelas.
                @else
                NIP: {{ $teacher->nip }}
                &bull; Bidang Studi:
                {{ $teacher->specialization ?? 'Guru' }}
                @endif
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            @if($isSuperAdmin)
            <button type="button" onclick="openScheduleModal()"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Jadwal
            </button>
            @endif

            <a href="{{ route('dashboard') }}"
                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition text-center">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- FILTER KHUSUS SUPER ADMIN -->
    @if($isSuperAdmin)
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">

        <h3 class="text-sm font-bold text-slate-900 mb-4">
            Filter Jadwal Mengajar
        </h3>

        <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 sm:grid-cols-2
    xl:grid-cols-4 gap-3">

            <select name="teacher_id" class="h-10 w-full rounded-xl border border-slate-300
                           px-3 text-sm text-slate-700 bg-white">
                <option value="">Semua Guru</option>
                @foreach($teachers as $item)
                <option value="{{ $item->id }}" @selected((string) request('teacher_id')===(string) $item->id)>
                    {{ $item->name }}
                </option>
                @endforeach
            </select>

            <select name="subject_id" class="h-10 w-full rounded-xl border border-slate-300
                           px-3 text-sm text-slate-700 bg-white">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($subjects as $item)
                <option value="{{ $item->id }}" @selected((string) request('subject_id')===(string) $item->id)>
                    {{ $item->name }}
                </option>
                @endforeach
            </select>

            <select name="class_id" class="h-10 w-full rounded-xl border border-slate-300
                           px-3 text-sm text-slate-700 bg-white">
                <option value="">Semua Kelas</option>
                @foreach($classes as $item)
                <option value="{{ $item->id }}" @selected((string) request('class_id')===(string) $item->id)>
                    {{ $item->name }}
                </option>
                @endforeach
            </select>

            <select name="day" class="h-10 w-full rounded-xl border border-slate-300
                           px-3 text-sm text-slate-700 bg-white">
                <option value="">Semua Hari</option>
                @foreach([
                'senin' => 'Senin',
                'selasa' => 'Selasa',
                'rabu' => 'Rabu',
                'kamis' => 'Kamis',
                'jumat' => 'Jumat',
                'sabtu' => 'Sabtu'
                ] as $value => $label)
                <option value="{{ $value }}" @selected(request('day')===$value)>
                    {{ $label }}
                </option>
                @endforeach
            </select>

            <div class="flex flex-wrap items-center gap-2 sm:col-span-2 xl:col-span-4">
                <button type="submit" class="inline-flex h-10 w-fit shrink-0 items-center justify-center
                       px-4 rounded-xl bg-slate-800 text-white text-xs font-bold
                       hover:bg-slate-900 cursor-pointer transition whitespace-nowrap">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Terapkan
                </button>

                <a href="{{ request()->url() }}" class="h-10 inline-flex items-center justify-center
                          px-4 rounded-xl border border-slate-300
                          text-slate-600 text-xs hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>
    @endif

    {{-- DAFTAR JADWAL --}}
    @foreach([
    'senin', 'selasa', 'rabu',
    'kamis', 'jumat', 'sabtu'
    ] as $day)

    @php
    $daySchedules = $schedules->get($day, collect());
    @endphp

    {{-- Hari kosong disembunyikan untuk admin --}}
    @if(!$isSuperAdmin || $daySchedules->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200
                    p-5 shadow-xs space-y-3">

        <div class="flex items-center justify-between
                        border-b border-slate-100 pb-2">
            <h3 class="text-sm font-bold text-slate-900 capitalize">
                <i class="fa-solid fa-calendar-day mr-2 text-blue-600"></i>
                Hari {{ ucfirst($day) }}
            </h3>

            <span class="text-xs font-semibold text-slate-400">
                {{ $daySchedules->count() }} Jadwal
            </span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($daySchedules as $sch)
            <div class="py-3 flex flex-col lg:flex-row
                                lg:items-center lg:justify-between
                                gap-3 text-xs">

                <div class="min-w-0">

                    @if($isSuperAdmin)
                    <div class="font-bold text-slate-900 text-sm mb-2">
                        <i class="fa-solid fa-user-tie mr-1 text-blue-600"></i>
                        {{ $sch->teacher->name ?? 'Guru tidak ditemukan' }}
                    </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md
                                             bg-blue-50 text-blue-700 font-bold">
                            {{ $sch->period_label }}
                        </span>

                        <span class="font-mono text-slate-500">
                            {{ $sch->time_range }} WIB
                        </span>

                        <span class="font-bold text-slate-900 text-sm">
                            {{ $sch->subject->name ?? '-' }}
                        </span>
                    </div>

                    <div class="text-slate-600 mt-1">
                        Kelas:
                        <strong class="text-indigo-700">
                            {{ $sch->schoolClass->name ?? '-' }}
                        </strong>

                        @if($isSuperAdmin && $sch->teacher)
                        <span class="text-slate-400 mx-1">|</span>
                        NIP:
                        {{ $sch->teacher->nip ?? '-' }}
                        @endif
                    </div>
                </div>

                @unless($isSuperAdmin)
                <form method="POST" action="{{ route('guru.sessions.open', $sch->id) }}" class="w-full lg:w-auto">
                    @csrf
                    <button type="submit" class="w-full lg:w-auto px-4 py-2
                                               rounded-xl bg-blue-600
                                               hover:bg-blue-700 text-white
                                               font-bold text-xs transition
                                               flex items-center justify-center">
                        <i class="fa-solid fa-qrcode mr-1.5"></i>
                        Buka Sesi QR
                    </button>
                </form>
                @endunless

                {{-- TOMBOL EDIT & HAPUS (SUPER ADMIN) --}}
                @if($isSuperAdmin)
                @php
                $schedulePayload = [
                    'teacher_id'   => $sch->teacher_id,
                    'subject_id'   => $sch->subject_id,
                    'class_id'     => $sch->class_id,
                    'day_of_week'  => $sch->day_of_week,
                    'period_start' => $sch->period_start,
                    'period_end'   => $sch->period_end,
                    'start_time'   => substr((string) $sch->start_time, 0, 5),
                    'end_time'     => substr((string) $sch->end_time, 0, 5),
                ];
                @endphp
                <div class="flex items-center gap-2">
                    <button type="button"
                        onclick="openScheduleModal(this)"
                        data-url="{{ route('admin.schedules.update', $sch->id) }}"
                        data-schedule="{{ json_encode($schedulePayload) }}"
                        class="px-3 py-2 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold text-xs transition">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                    </button>

                    <form method="POST" action="{{ route('admin.schedules.destroy', $sch->id) }}"
                        onsubmit="return confirm('Yakin mau hapus jadwal ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 font-bold text-xs transition">
                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="py-5 text-center text-xs text-slate-400">
                Tidak ada jadwal mengajar pada hari
                {{ ucfirst($day) }}.
            </div>
            @endforelse
        </div>
    </div>
    @endif
    @endforeach

    {{-- Pesan ketika filter admin tidak menemukan hasil --}}
    @if($isSuperAdmin && $schedules->flatten(1)->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200
                    p-10 text-center">
        <i class="fa-solid fa-calendar-xmark text-3xl text-slate-300"></i>
        <p class="mt-3 text-sm font-bold text-slate-700">
            Jadwal tidak ditemukan
        </p>
        <p class="mt-1 text-xs text-slate-500">
            Coba ubah filter guru, mata pelajaran, kelas, atau hari.
        </p>
    </div>
    @endif

</div>

{{-- MODAL TAMBAH / EDIT JADWAL (SUPER ADMIN) --}}
@if($isSuperAdmin)
<div id="scheduleModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl max-h-[90vh] overflow-y-auto">
        <h3 id="scheduleModalTitle" class="text-base font-bold text-slate-900 mb-4">Tambah Jadwal</h3>

        <form id="scheduleForm" method="POST" action="{{ route('admin.schedules.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="_method" id="scheduleMethod" value="POST">

            @php $sel = 'h-10 w-full rounded-xl border border-slate-300 px-3 text-sm text-slate-700 bg-white'; @endphp

            <div>
                <label class="text-xs font-semibold text-slate-600">Guru</label>
                <select name="teacher_id" id="f_teacher_id" class="{{ $sel }}" required>
                    <option value="">Pilih guru</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-600">Mata Pelajaran</label>
                <select name="subject_id" id="f_subject_id" class="{{ $sel }}" required>
                    <option value="">Pilih mata pelajaran</option>
                    @foreach($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Kelas</label>
                    <select name="class_id" id="f_class_id" class="{{ $sel }}" required>
                        <option value="">Pilih kelas</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Hari</label>
                    <select name="day_of_week" id="f_day_of_week" class="{{ $sel }}" required>
                        @foreach(['senin','selasa','rabu','kamis','jumat','sabtu'] as $d)
                        <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Jam Ke (mulai)</label>
                    <input type="number" min="1" max="12" name="period_start" id="f_period_start" class="{{ $sel }}" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Jam Ke (selesai)</label>
                    <input type="number" min="1" max="12" name="period_end" id="f_period_end" class="{{ $sel }}" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Jam Mulai</label>
                    <input type="time" name="start_time" id="f_start_time" class="{{ $sel }}" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Jam Selesai</label>
                    <input type="time" name="end_time" id="f_end_time" class="{{ $sel }}" required>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="closeScheduleModal()"
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 text-xs hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const scheduleModal = document.getElementById('scheduleModal');
    const scheduleForm  = document.getElementById('scheduleForm');
    const scheduleFields = ['teacher_id','subject_id','class_id','day_of_week','period_start','period_end','start_time','end_time'];

    function openScheduleModal(btn = null) {
        scheduleForm.reset();

        if (btn) {
            const data = JSON.parse(btn.dataset.schedule);
            scheduleForm.action = btn.dataset.url;
            document.getElementById('scheduleMethod').value = 'PUT';
            document.getElementById('scheduleModalTitle').textContent = 'Edit Jadwal';
            scheduleFields.forEach(f => {
                document.getElementById('f_' + f).value = data[f] ?? '';
            });
        } else {
            scheduleForm.action = "{{ route('admin.schedules.store') }}";
            document.getElementById('scheduleMethod').value = 'POST';
            document.getElementById('scheduleModalTitle').textContent = 'Tambah Jadwal';
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
@endif

@endsection