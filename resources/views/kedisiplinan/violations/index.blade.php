@extends('layouts.app')

@section('title', 'Catatan Pelanggaran Siswa')
@section('page_title', 'Catatan Pelanggaran & Kedisiplinan Siswa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Data Riwayat Pelanggaran Tata Tertib</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catat kronologi kejadian, siswa yang terlibat, penambahan poin, dan tindak lanjut pembinaan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('kedisiplinan.violations.export') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition flex items-center">
                <i class="fa-solid fa-download mr-1.5 text-blue-600"></i> Ekspor Laporan CSV
            </a>
            <button type="button" onclick="openRecordViolationModal()"
                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition flex items-center">
                <i class="fa-solid fa-plus mr-1.5"></i> Catat Pelanggaran Baru
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('kedisiplinan.violations.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Cari Siswa</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIS..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
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
                <label class="block text-slate-500 mb-1 font-semibold">Status Penanganan</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="tercatat" {{ request('status') === 'tercatat' ? 'selected' : '' }}>Tercatat</option>
                    <option value="dalam_pembinaan" {{ request('status') === 'dalam_pembinaan' ? 'selected' : '' }}>Dalam Pembinaan</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="panggilan_ortu" {{ request('status') === 'panggilan_ortu' ? 'selected' : '' }}>Panggilan Orang Tua</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('kedisiplinan.violations.index') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
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
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Siswa Terlibat</th>
                        <th class="py-3 px-4">Jenis Pelanggaran</th>
                        <th class="py-3 px-4">Poin</th>
                        <th class="py-3 px-4">Kronologi & Lokasi</th>
                        <th class="py-3 px-4">Status & Tindak Lanjut</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($violations as $v)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $v->incident_date->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $v->incident_time ? substr($v->incident_time, 0, 5) . ' WIB' : '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $v->student->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $v->student->nis ?? '-' }} &bull; {{ $v->student->schoolClass->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $v->violationType->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $v->violationType->category ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-black text-rose-600">+{{ $v->points }}</td>
                            <td class="py-3 px-4 max-w-xs">
                                <div class="text-slate-800 line-clamp-2">{{ $v->chronology }}</div>
                                @if($v->location)
                                    <div class="text-[11px] text-slate-400 mt-0.5"><i class="fa-solid fa-location-dot mr-1"></i> {{ $v->location }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div>{!! $v->status_badge !!}</div>
                                @if($v->follow_up_action)
                                    <div class="text-[11px] text-slate-500 mt-1 line-clamp-1">{{ $v->follow_up_action }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('kedisiplinan.summons.create', ['violation_id' => $v->id]) }}"
                                    title="Buat Surat Panggilan Orang Tua"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-[11px] transition">
                                    <i class="fa-solid fa-envelope-open-text mr-1"></i> Panggilan Ortu
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">Belum ada catatan pelanggaran yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $violations->links() }}
        </div>
    </div>
</div>

<!-- Modal Catat Pelanggaran -->
<div id="recordViolationModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Catat Pelanggaran Siswa Baru</h3>
            <button type="button" onclick="closeRecordViolationModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('kedisiplinan.violations.store') }}" class="space-y-3.5">
            @csrf

            {{-- Komponen Live Search Siswa --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Cari & Pilih Siswa</label>
                <div class="relative" id="violationStudentSearchWrapper">
                    <div class="flex items-center border border-slate-300 rounded-xl px-3 py-2 focus-within:border-blue-500 bg-white">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs mr-2"></i>
                        <input
                            type="text"
                            id="violationStudentSearchInput"
                            placeholder="Ketik nama atau NIS siswa..."
                            autocomplete="off"
                            class="flex-1 text-xs focus:outline-none bg-transparent text-slate-800"
                            oninput="searchStudentForViolation(this.value)"
                        >
                        <button type="button" id="violationClearStudent" onclick="clearViolationStudent()" class="hidden text-slate-400 hover:text-rose-500 ml-1">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    {{-- Dropdown hasil pencarian --}}
                    <div id="violationStudentDropdown"
                        class="absolute z-50 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-52 overflow-y-auto hidden">
                    </div>
                    {{-- Kartu siswa terpilih --}}
                    <div id="violationSelectedStudentCard" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-blue-900" id="violationSelectedStudentName"></div>
                            <div class="text-[11px] text-blue-600 mt-0.5" id="violationSelectedStudentInfo"></div>
                        </div>
                        <button type="button" onclick="clearViolationStudent()" class="text-blue-400 hover:text-rose-500 ml-3 text-xs font-semibold">
                            <i class="fa-solid fa-rotate-left mr-1"></i>Ganti
                        </button>
                    </div>
                </div>
                <input type="hidden" name="student_id" id="violationStudentId" required>
                <p id="violationStudentNotFound" class="hidden mt-1.5 text-xs text-slate-400 italic">
                    <i class="fa-solid fa-circle-info mr-1 text-amber-500"></i>Data siswa tidak ditemukan. Coba kata kunci lain.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Jenis Pelanggaran</label>
                <select name="violation_type_id" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    @foreach($violationTypes as $vt)
                        <option value="{{ $vt->id }}">{{ $vt->name }} (+{{ $vt->points }} poin - {{ ucfirst($vt->category) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Tanggal Kejadian</label>
                    <input type="date" name="incident_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Waktu Kejadian (WIB)</label>
                    <input type="time" name="incident_time" value="{{ date('H:i') }}" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Lokasi Kejadian</label>
                <input type="text" name="location" placeholder="Contoh: Toilet siswa lt. 2, Luar pagar sekolah, Kantin..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Kronologi Lengkap Kejadian</label>
                <textarea name="chronology" rows="3" required placeholder="Jelaskan secara rinci kronologi kejadian pelanggaran..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Tindak Lanjut / Sanksi</label>
                <input type="text" name="follow_up_action" placeholder="Teguran, pembinaan BK, pemanggilan ortu..." class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700">Status Penanganan</label>
                <select name="status" required class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
                    <option value="tercatat">Tercatat</option>
                    <option value="dalam_pembinaan">Dalam Pembinaan BK/Walas</option>
                    <option value="panggilan_ortu">Perlu Panggilan Orang Tua</option>
                    <option value="selesai">Selesai / Dituntaskan</option>
                </select>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeRecordViolationModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white">Simpan Pelanggaran</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// =============================================
// Live Search Siswa - Modal Catat Pelanggaran
// =============================================
const VIOLATION_SEARCH_URL = '{{ route("kedisiplinan.students.search") }}';
let violationSearchTimer = null;

function openRecordViolationModal() {
    document.getElementById('recordViolationModal').classList.remove('hidden');
    // Reset live search state
    clearViolationStudent();
    document.getElementById('violationStudentSearchInput').value = '';
}
function closeRecordViolationModal() {
    document.getElementById('recordViolationModal').classList.add('hidden');
}

function searchStudentForViolation(query) {
    clearTimeout(violationSearchTimer);
    const dropdown = document.getElementById('violationStudentDropdown');
    const notFound = document.getElementById('violationStudentNotFound');

    if (query.trim().length < 1) {
        dropdown.classList.add('hidden');
        notFound.classList.add('hidden');
        return;
    }

    violationSearchTimer = setTimeout(() => {
        fetch(`${VIOLATION_SEARCH_URL}?q=${encodeURIComponent(query)}`, {
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
                item.onclick = () => selectViolationStudent(student);
                dropdown.appendChild(item);
            });

            dropdown.classList.remove('hidden');
        })
        .catch(() => {
            dropdown.classList.add('hidden');
        });
    }, 300);
}

function selectViolationStudent(student) {
    document.getElementById('violationStudentId').value = student.id;
    document.getElementById('violationStudentSearchInput').value = student.name;
    document.getElementById('violationStudentSearchInput').disabled = true;
    document.getElementById('violationClearStudent').classList.remove('hidden');

    document.getElementById('violationSelectedStudentName').textContent = student.name;
    document.getElementById('violationSelectedStudentInfo').textContent =
        `NIS: ${student.nis} · ${student.class_name}${student.total_points > 0 ? ` · Total poin: ${student.total_points}` : ''}`;

    document.getElementById('violationSelectedStudentCard').classList.remove('hidden');
    document.getElementById('violationStudentDropdown').classList.add('hidden');
    document.getElementById('violationStudentNotFound').classList.add('hidden');
}

function clearViolationStudent() {
    document.getElementById('violationStudentId').value = '';
    document.getElementById('violationStudentSearchInput').value = '';
    document.getElementById('violationStudentSearchInput').disabled = false;
    document.getElementById('violationStudentSearchInput').focus();
    document.getElementById('violationClearStudent').classList.add('hidden');
    document.getElementById('violationSelectedStudentCard').classList.add('hidden');
    document.getElementById('violationStudentDropdown').classList.add('hidden');
    document.getElementById('violationStudentNotFound').classList.add('hidden');
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('violationStudentSearchWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('violationStudentDropdown').classList.add('hidden');
    }
});
</script>
@endpush
@endsection
