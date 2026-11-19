@extends('layouts.app')

@section('title', 'Buat Surat Panggilan Orang Tua')
@section('page_title', 'Penerbitan Surat Panggilan Orang Tua')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-5">
            <h2 class="text-base font-bold text-slate-900">Formulir Penerbitan Surat Panggilan Resmi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Surat ini akan dicetak dalam format dinas resmi berkop surat SMAN 5 Karawang.</p>
        </div>

        <form method="POST" action="{{ route('kedisiplinan.summons.store') }}" class="space-y-4" id="summonForm">
            @csrf
            @if($selectedViolation)
                <input type="hidden" name="violation_id" value="{{ $selectedViolation->id }}">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Nomor Surat Dinas</label>
                    <input type="text" name="letter_number" value="{{ old('letter_number', $letterNumber) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                </div>

                {{-- Komponen Live Search Siswa --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Cari & Pilih Siswa
                        @if($selectedViolation)
                            <span class="text-blue-600 font-normal ml-1">(sudah terpilih dari pelanggaran)</span>
                        @endif
                    </label>
                    <div class="relative" id="summonStudentSearchWrapper">
                        @if($student && !old('student_id'))
                            {{-- Siswa sudah dipilih dari context violation --}}
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-blue-900">{{ $student->name }}</div>
                                    <div class="text-[11px] text-blue-600 mt-0.5">NIS: {{ $student->nis }} · {{ $student->schoolClass?->name ?? '-' }}</div>
                                </div>
                                <span class="text-xs font-semibold text-blue-500 bg-blue-100 px-2 py-1 rounded-lg">
                                    <i class="fa-solid fa-link mr-1"></i>Dari Pelanggaran
                                </span>
                            </div>
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                        @else
                            <div class="flex items-center border border-slate-300 rounded-xl px-3 py-2 focus-within:border-blue-500 bg-white">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs mr-2"></i>
                                <input
                                    type="text"
                                    id="summonStudentSearchInput"
                                    placeholder="Ketik nama atau NIS siswa..."
                                    autocomplete="off"
                                    class="flex-1 text-xs focus:outline-none bg-transparent text-slate-800"
                                    oninput="searchStudentForSummon(this.value)"
                                >
                                <button type="button" id="summonClearStudent" onclick="clearSummonStudent()" class="hidden text-slate-400 hover:text-rose-500 ml-1">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                            {{-- Dropdown hasil pencarian --}}
                            <div id="summonStudentDropdown"
                                class="absolute z-50 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-52 overflow-y-auto hidden">
                            </div>
                            {{-- Kartu siswa terpilih --}}
                            <div id="summonSelectedStudentCard" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-blue-900" id="summonSelectedStudentName"></div>
                                    <div class="text-[11px] text-blue-600 mt-0.5" id="summonSelectedStudentInfo"></div>
                                </div>
                                <button type="button" onclick="clearSummonStudent()" class="text-blue-400 hover:text-rose-500 ml-3 text-xs font-semibold">
                                    <i class="fa-solid fa-rotate-left mr-1"></i>Ganti
                                </button>
                            </div>
                            <input type="hidden" name="student_id" id="summonStudentId" value="{{ old('student_id') }}" required>
                            <p id="summonStudentNotFound" class="hidden mt-1.5 text-xs text-slate-400 italic">
                                <i class="fa-solid fa-circle-info mr-1 text-amber-500"></i>Data siswa tidak ditemukan. Coba kata kunci lain.
                            </p>

                            {{-- Jika ada old('student_id'), tampilkan sekedar info --}}
                            @if(old('student_id'))
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Auto-fill jika ada validasi error dan student_id tersimpan
                                    var oldId = "{{ old('student_id') }}";
                                    if (oldId) {
                                        document.getElementById('summonStudentId').value = oldId;
                                    }
                                });
                                </script>
                            @endif
                        @endif
                    </div>
                    {{-- Tambahkan input wali kelas agar auto-terisi setelah siswa terpilih --}}
                    <input type="hidden" id="summonHomeroomName" value="{{ $student?->schoolClass?->homeroomTeacher?->name ?? '' }}">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Ditujukan Kepada (Nama Orang Tua / Wali)</label>
                <input type="text" name="recipient_parent_name" value="{{ old('recipient_parent_name', 'Bapak / Ibu Orang Tua / Wali Siswa') }}" placeholder="Contoh: Bapak/Ibu Orang Tua/Wali Siswa"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Alasan Pemanggilan</label>
                <textarea name="reason" rows="3" required placeholder="Jelaskan alasan pemanggilan orang tua..."
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">{{ old('reason', $selectedViolation ? 'Konsultasi pembinaan kedisiplinan terkait pelanggaran tata tertib sekolah (' . $selectedViolation->violationType->name . ').' : '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Tanggal Pertemuan</label>
                    <input type="date" name="meeting_date" value="{{ old('meeting_date', date('Y-m-d', strtotime('+1 day'))) }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Waktu Pertemuan (WIB)</label>
                    <input type="time" name="meeting_time" value="{{ old('meeting_time', '09:00') }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Tempat Pertemuan</label>
                    <input type="text" name="meeting_place" value="{{ old('meeting_place', 'Ruang BP/BK SMAN 5 Karawang') }}" required
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <!-- Tanda Tangan Pejabat Terkait -->
            <div class="pt-2 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Tanda Tangan Pihak Terkait</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Jabatan Penandatangan</label>
                        <input type="text" name="acknowledged_by_title" value="{{ old('acknowledged_by_title', 'Wakasek Bidang Kesiswaan') }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nama Penandatangan</label>
                        <input type="text" name="acknowledged_by_name" value="{{ old('acknowledged_by_name', 'Drs. H. Mulyadi, M.Pd.') }}" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">NIP Penandatangan</label>
                        <input type="text" name="acknowledged_by_nip" value="{{ old('acknowledged_by_nip', '197508102005011002') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Nama Wali Kelas (Opsional)</label>
                        <input type="text" name="homeroom_teacher_name" value="{{ old('homeroom_teacher_name', $student?->schoolClass?->homeroomTeacher?->name ?? '') }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    </div>
                </div>
            </div>

            <input type="hidden" name="status" value="dikirim">

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('kedisiplinan.summons.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-600/20">
                    <i class="fa-solid fa-print mr-1.5"></i> Terbitkan & Siapkan Cetak
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// =============================================
// Live Search Siswa - Form Surat Panggilan
// =============================================
const SUMMON_SEARCH_URL = '{{ route("kedisiplinan.students.search") }}';
let summonSearchTimer = null;

function searchStudentForSummon(query) {
    clearTimeout(summonSearchTimer);
    const dropdown = document.getElementById('summonStudentDropdown');
    const notFound = document.getElementById('summonStudentNotFound');

    if (!dropdown) return;

    if (query.trim().length < 1) {
        dropdown.classList.add('hidden');
        notFound.classList.add('hidden');
        return;
    }

    summonSearchTimer = setTimeout(() => {
        fetch(`${SUMMON_SEARCH_URL}?q=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(students => {
            dropdown.innerHTML = '';
            notFound.classList.add('hidden');

            if (students.length === 0) {
                dropdown.classList.add('hidden');
                notFound.classList.remove('hidden');
                return;
            }

            students.forEach(student => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'w-full text-left px-4 py-2.5 hover:bg-blue-50 transition border-b border-slate-100 last:border-0';
                item.innerHTML = `
                    <div class="font-semibold text-xs text-slate-900">${student.name}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        <span class="font-mono">${student.nis}</span>
                        &bull; ${student.class_name}
                        ${student.total_points > 0 ? `&bull; <span class="text-rose-600 font-semibold">${student.total_points} poin</span>` : ''}
                    </div>
                `;
                item.onclick = () => selectSummonStudent(student);
                dropdown.appendChild(item);
            });

            dropdown.classList.remove('hidden');
        })
        .catch(() => {
            dropdown.classList.add('hidden');
        });
    }, 300);
}

function selectSummonStudent(student) {
    const hiddenInput = document.getElementById('summonStudentId');
    const searchInput = document.getElementById('summonStudentSearchInput');
    const dropdown = document.getElementById('summonStudentDropdown');
    const card = document.getElementById('summonSelectedStudentCard');
    const nameEl = document.getElementById('summonSelectedStudentName');
    const infoEl = document.getElementById('summonSelectedStudentInfo');
    const homeroomField = document.getElementById('homeroomTeacherNameField');

    if (hiddenInput) hiddenInput.value = student.id;
    if (searchInput) {
        searchInput.value = student.name;
        searchInput.disabled = true;
    }

    document.getElementById('summonClearStudent')?.classList.remove('hidden');
    if (nameEl) nameEl.textContent = student.name;
    if (infoEl) infoEl.textContent = `NIS: ${student.nis} · ${student.class_name}`;
    if (card) card.classList.remove('hidden');
    if (dropdown) dropdown.classList.add('hidden');
    document.getElementById('summonStudentNotFound')?.classList.add('hidden');
}

function clearSummonStudent() {
    const hiddenInput = document.getElementById('summonStudentId');
    const searchInput = document.getElementById('summonStudentSearchInput');
    const card = document.getElementById('summonSelectedStudentCard');

    if (hiddenInput) hiddenInput.value = '';
    if (searchInput) {
        searchInput.value = '';
        searchInput.disabled = false;
        searchInput.focus();
    }
    document.getElementById('summonClearStudent')?.classList.add('hidden');
    if (card) card.classList.add('hidden');
    document.getElementById('summonStudentDropdown')?.classList.add('hidden');
    document.getElementById('summonStudentNotFound')?.classList.add('hidden');
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('summonStudentSearchWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('summonStudentDropdown')?.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
