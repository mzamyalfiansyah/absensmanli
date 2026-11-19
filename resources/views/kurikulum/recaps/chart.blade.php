@extends('layouts.app')

@section('title', 'Grafik Absensi Bulanan')
@section('page_title', 'Statistik & Grafik Absensi')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <i class="fa-solid fa-chart-column mr-1.5"></i>
                    {{ $month ? $monthNames[$month] . ' ' . $year : 'Tahun ' . $year }}
                </span>
                @if($classId && $classes->firstWhere('id', $classId))
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    <i class="fa-solid fa-chalkboard-user mr-1.5"></i> {{ $classes->firstWhere('id', $classId)->name }}
                </span>
                @else
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    Semua Kelas
                </span>
                @endif
            </div>
            <h2 class="text-base font-bold text-slate-900 mt-2">Visualisasi Grafik Absensi Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tren statistik kehadiran per bulan berdasarkan data aktual database
                SMAN 5 Karawang.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('kurikulum.recaps.monthly', ['year' => $year, 'class_id' => $classId]) }}"
                class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md shadow-slate-900/10 transition flex items-center">
                <i class="fa-solid fa-table-list mr-1.5"></i> Lihat Rincian Rekap Tabel
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('kurikulum.recaps.chart') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Tahun</label>
                <select name="year" id="filterYear"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:outline-none">
                    @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ (int)$year === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Bulan</label>
                <select name="month" id="filterMonth"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:outline-none">
                    <option value="">Semua Bulan</option>
                    @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ (int)$month === $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Kelas</label>
                <select name="class_id" id="filterClass"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-indigo-600 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition flex items-center justify-center">
                    <i class="fa-solid fa-filter mr-1.5"></i> Tampilkan Grafik
                </button>
                <a href="{{ route('kurikulum.recaps.chart') }}"
                    class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards for Year -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-emerald-600 uppercase">Hadir
                    ({{ $month ? 'Bulanan' : 'Tahunan' }})</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div id="statHadir" class="text-2xl font-extrabold text-emerald-700 mt-1">0</div>
            <span class="text-[10px] text-slate-400">Total sesi hadir</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-amber-600 uppercase">Terlambat</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
            <div id="statTerlambat" class="text-2xl font-extrabold text-amber-700 mt-1">0</div>
            <span class="text-[10px] text-slate-400">Total terlambat</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-blue-600 uppercase">Izin</span>
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            </div>
            <div id="statIzin" class="text-2xl font-extrabold text-blue-700 mt-1">0</div>
            <span class="text-[10px] text-slate-400">Surat izin sah</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-purple-600 uppercase">Sakit</span>
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
            </div>
            <div id="statSakit" class="text-2xl font-extrabold text-purple-700 mt-1">0</div>
            <span class="text-[10px] text-slate-400">Surat sakit</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-rose-600 uppercase">Alpa</span>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            </div>
            <div id="statAlpa" class="text-2xl font-extrabold text-rose-700 mt-1">0</div>
            <span class="text-[10px] text-slate-400">Tanpa keterangan</span>
        </div>
    </div>

    <!-- Chart Main Container -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-800">
                    @if($month)
                    Distribusi Kehadiran Harian ({{ $monthNames[$month] }} {{ $year }})
                    @else
                    Distribusi Kehadiran Bulanan (Januari - Desember {{ $year }})
                    @endif
                </h3>
                <p class="text-xs text-slate-400">Grafik menampilkan 5 kategori status absensi siswa per bulan</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-500 font-medium">Tipe Grafik:</span>
                <button type="button" id="btnTypeBar" onclick="setChartType('bar')"
                    class="px-2.5 py-1 text-xs rounded-lg font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 transition">
                    <i class="fa-solid fa-chart-bar mr-1"></i> Batang
                </button>
                <button type="button" id="btnTypeLine" onclick="setChartType('line')"
                    class="px-2.5 py-1 text-xs rounded-lg font-medium text-slate-600 hover:bg-slate-100 border border-slate-200 transition">
                    <i class="fa-solid fa-chart-line mr-1"></i> Garis
                </button>
            </div>
        </div>

        <!-- Canvas and Loading Overlay -->
        <div class="relative w-full" style="min-height: 380px;">
            <div id="chartLoading"
                class="absolute inset-0 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center z-10 transition-opacity">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-2"></div>
                <span class="text-xs font-semibold text-slate-600">Memuat data grafik...</span>
            </div>
            <div id="chartEmptyMessage"
                class="hidden absolute inset-0 flex flex-col items-center justify-center z-10 text-center p-6">
                <i class="fa-solid fa-chart-simple text-4xl text-slate-300 mb-2"></i>
                <p class="text-sm font-bold text-slate-700">Belum ada data absensi tercatat pada
                    {{ $month ? strtolower($monthNames[$month]) . ' ' . $year : 'tahun ' . $year }}</p>
                <p class="text-xs text-slate-400 mt-1">Data akan muncul secara otomatis setelah sesi absensi
                    dilaksanakan.</p>
            </div>
            <canvas id="monthlyAttendanceCanvas" class="w-full" style="max-height: 420px;"
                data-daily="{{ $month ? '1' : '0' }}"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
let attendanceChartInstance = null;
let currentChartType = 'bar';
let cachedChartData = null;

const chartEndpoint =
    "{{ route('kurikulum.recaps.chart_data') }}?year={{ $year }}&month={{ $month }}&class_id={{ $classId }}";

const isDailyMode = document.getElementById('monthlyAttendanceCanvas').dataset.daily === '1';

function setChartType(type) {
    if (currentChartType === type) return;
    currentChartType = type;

    const btnBar = document.getElementById('btnTypeBar');
    const btnLine = document.getElementById('btnTypeLine');

    if (type === 'bar') {
        btnBar.className =
            'px-2.5 py-1 text-xs rounded-lg font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 transition';
        btnLine.className =
            'px-2.5 py-1 text-xs rounded-lg font-medium text-slate-600 hover:bg-slate-100 border border-slate-200 transition';
    } else {
        btnLine.className =
            'px-2.5 py-1 text-xs rounded-lg font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 transition';
        btnBar.className =
            'px-2.5 py-1 text-xs rounded-lg font-medium text-slate-600 hover:bg-slate-100 border border-slate-200 transition';
    }

    if (cachedChartData) {
        renderChart(cachedChartData);
    }
}

async function loadChartData() {
    const loadingEl = document.getElementById('chartLoading');
    const emptyEl = document.getElementById('chartEmptyMessage');
    loadingEl.classList.remove('hidden');

    try {
        const res = await fetch(chartEndpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) throw new Error('Gagal memuat data dari server');

        const data = await res.json();
        cachedChartData = data;

        // Hitung total akumulasi tahunan untuk KPI cards
        const hadirTotal = (data.datasets.hadir || []).reduce((a, b) => a + b, 0);
        const terlambatTotal = (data.datasets.terlambat || []).reduce((a, b) => a + b, 0);
        const izinTotal = (data.datasets.izin || []).reduce((a, b) => a + b, 0);
        const sakitTotal = (data.datasets.sakit || []).reduce((a, b) => a + b, 0);
        const alpaTotal = (data.datasets.alpa || []).reduce((a, b) => a + b, 0);

        document.getElementById('statHadir').textContent = hadirTotal.toLocaleString('id-ID');
        document.getElementById('statTerlambat').textContent = terlambatTotal.toLocaleString('id-ID');
        document.getElementById('statIzin').textContent = izinTotal.toLocaleString('id-ID');
        document.getElementById('statSakit').textContent = sakitTotal.toLocaleString('id-ID');
        document.getElementById('statAlpa').textContent = alpaTotal.toLocaleString('id-ID');

        const grandTotal = hadirTotal + terlambatTotal + izinTotal + sakitTotal + alpaTotal;
        if (grandTotal === 0) {
            emptyEl.classList.remove('hidden');
        } else {
            emptyEl.classList.add('hidden');
        }

        renderChart(data);
    } catch (err) {
        console.error('Error fetching chart data:', err);
        emptyEl.classList.remove('hidden');
        emptyEl.innerHTML = `
                <i class="fa-solid fa-triangle-exclamation text-4xl text-rose-400 mb-2"></i>
                <p class="text-sm font-bold text-slate-700">Terjadi kesalahan memuat data grafik</p>
                <p class="text-xs text-slate-400 mt-1">${err.message}</p>
            `;
    } finally {
        loadingEl.classList.add('hidden');
    }
}

function renderChart(data) {
    const ctx = document.getElementById('monthlyAttendanceCanvas');
    if (!ctx) return;

    if (attendanceChartInstance) {
        attendanceChartInstance.destroy();
    }

    const isBar = currentChartType === 'bar';

    const datasetsConfig = [{
            label: 'Hadir',
            data: data.datasets.hadir || [],
            backgroundColor: isBar ? 'rgba(16, 185, 129, 0.85)' : 'rgba(16, 185, 129, 0.15)',
            borderColor: '#059669',
            borderWidth: 2,
            borderRadius: isBar ? 6 : 0,
            fill: !isBar,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
        },
        {
            label: 'Terlambat',
            data: data.datasets.terlambat || [],
            backgroundColor: isBar ? 'rgba(245, 158, 11, 0.85)' : 'rgba(245, 158, 11, 0.15)',
            borderColor: '#d97706',
            borderWidth: 2,
            borderRadius: isBar ? 6 : 0,
            fill: !isBar,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
        },
        {
            label: 'Izin',
            data: data.datasets.izin || [],
            backgroundColor: isBar ? 'rgba(59, 130, 246, 0.85)' : 'rgba(59, 130, 246, 0.15)',
            borderColor: '#2563eb',
            borderWidth: 2,
            borderRadius: isBar ? 6 : 0,
            fill: !isBar,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
        },
        {
            label: 'Sakit',
            data: data.datasets.sakit || [],
            backgroundColor: isBar ? 'rgba(139, 92, 246, 0.85)' : 'rgba(139, 92, 246, 0.15)',
            borderColor: '#7c3aed',
            borderWidth: 2,
            borderRadius: isBar ? 6 : 0,
            fill: !isBar,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
        },
        {
            label: 'Alpa',
            data: data.datasets.alpa || [],
            backgroundColor: isBar ? 'rgba(244, 63, 94, 0.85)' : 'rgba(244, 63, 94, 0.15)',
            borderColor: '#e11d48',
            borderWidth: 2,
            borderRadius: isBar ? 6 : 0,
            fill: !isBar,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
        }
    ];

    attendanceChartInstance = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: data.months || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov',
                'Des'
            ],
            datasets: datasetsConfig
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        padding: 18,
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleFont: {
                        size: 13,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 12
                    },
                    padding: 12,
                    cornerRadius: 10,
                    usePointStyle: true,
                    callbacks: {
                        title: function(items) {
                            if (!items.length) return '';
                            return isDailyMode ? 'Tanggal ' + items[0].label : items[0].label;
                        },
                        footer: function(tooltipItems) {
                            let sum = 0;
                            tooltipItems.forEach(item => {
                                sum += item.parsed.y;
                            });
                            return 'Total Sesi: ' + sum;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.7)'
                    }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadChartData();
});
</script>
@endpush