@extends('layouts.app')

@section('title', 'Dashboard SuperAdmin')
@section('page_title', 'Dashboard Super Administrator')

@section('content')
<div class="space-y-6">
    <!-- Stat Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Total
                    Akun</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['total_users']) }}</div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Total
                    Siswa</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['total_students']) }}</div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Total
                    Guru</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['total_teachers']) }}</div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-school"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Total
                    Rombel</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['total_classes']) }}</div>
            </div>
        </div>
    </div>

    <!-- Second Stat Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Absen
                    Siswa Hari Ini</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['today_student_attendances']) }}</div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">
                    Kehadiran Guru</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['today_teacher_attendances']) }}</div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">
                    Laporan Pending</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['pending_condition_reports']) }}</div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-3.5 sm:p-5 border border-slate-200 shadow-xs flex items-center space-x-2.5 sm:space-x-4">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">
                    Pelanggaran</div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                    {{ number_format($stats['total_violations']) }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Panel -->
    <div
        class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-4 sm:p-6 text-white shadow-lg shadow-blue-900/10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-extrabold tracking-tight">Pusat Kendali Administrator SMAN 5 Karawang
            </h2>
            <p class="text-xs text-blue-100 mt-1">Konfigurasikan titik koordinat GPS radius sekolah, tahun ajaran aktif,
                dan kelola akun pengguna.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <a href="{{ route('admin.settings') }}"
                class="w-full sm:w-auto justify-center px-4 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <i class="fa-solid fa-location-crosshairs mr-1.5 text-rose-600"></i> Atur Koordinat & Radius
            </a>
            <a href="{{ route('admin.users.index') }}"
                class="w-full sm:w-auto justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center border border-blue-400">
                <i class="fa-solid fa-user-plus mr-1.5"></i> Kelola Pengguna
            </a>
            <a href="{{ route('admin.master.schedules') }}"
                class="w-full sm:w-auto justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center border border-blue-400">
                <i class="fa-solid fa-calendar-plus mr-1.5"></i> Atur Jadwal
            </a>
        </div>
    </div>


    <!-- Grafik Absensi Bulan Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-chart-column mr-2 text-indigo-600"></i> Grafik Absensi Siswa
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Distribusi kehadiran harian bulan {{ now()->translatedFormat('F Y') }}
                </p>
            </div>
            <a href="{{ route('kurikulum.recaps.chart', ['year' => now()->year, 'month' => now()->month]) }}"
                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center justify-center shadow-xs">
                Lihat Detail <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </a>
        </div>

        <div class="relative w-full" style="height: 300px;">
            <div id="dashChartLoading"
                class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center z-10">
                <div class="animate-spin rounded-full h-7 w-7 border-b-2 border-indigo-600 mb-2"></div>
                <span class="text-xs font-semibold text-slate-600">Memuat grafik...</span>
            </div>
            <div id="dashChartEmpty"
                class="hidden absolute inset-0 flex flex-col items-center justify-center z-10 text-center">
                <i class="fa-solid fa-chart-simple text-3xl text-slate-300 mb-2"></i>
                <p class="text-sm font-bold text-slate-700">Belum ada data absensi bulan ini</p>
            </div>
            <canvas id="dashAttendanceCanvas"
                data-endpoint="{{ route('kurikulum.recaps.chart_data', ['year' => now()->year, 'month' => now()->month]) }}"></canvas>
        </div>
    </div>

    <!-- Sesi Absensi Aktif Hari Ini & Audit Log Terkini -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sesi Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-qrcode mr-2 text-blue-600"></i> Sesi QR Absensi Hari Ini
                </h3>
                <span class="text-xs text-slate-400">{{ count($recentSessions) }} sesi tercatat</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentSessions as $session)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-slate-800">
                            {{ $session->schedule->subject->name ?? 'Pelajaran' }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Kelas: <strong>{{ $session->schedule->schoolClass->name ?? '-' }}</strong> &bull; Guru:
                            {{ $session->teacher->name ?? '-' }}
                        </div>
                    </div>
                    <span
                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $session->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($session->status) }}
                    </span>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-slate-400">Belum ada sesi absensi yang dibuka hari ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Log Aktivitas Terkini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-shield-halved mr-2 text-indigo-600"></i> Log Aktivitas Terkini
                </h3>
                <a href="{{ route('admin.audit_logs') }}"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentLogs as $log)
                <div class="py-2.5 flex items-start justify-between text-xs">
                    <div>
                        <span class="font-bold text-slate-800">{{ $log->user->name ?? 'Sistem' }}</span>
                        <span class="text-slate-500 ml-1">({{ $log->action }})</span>
                        <div class="text-slate-500 text-[11px] mt-0.5 truncate max-w-xs">
                            {{ is_array($log->details) ? json_encode($log->details) : $log->details }}
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 whitespace-nowrap ml-2">
                        {{ $log->created_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="py-8 text-center text-xs text-slate-400">Belum ada log aktivitas.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const canvas = document.getElementById('dashAttendanceCanvas');
    const loading = document.getElementById('dashChartLoading');
    const empty = document.getElementById('dashChartEmpty');
    if (!canvas) return;

    try {
        const res = await fetch(canvas.dataset.endpoint, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!res.ok) throw new Error('Gagal memuat data');
        const data = await res.json();

        const series = [{
                key: 'hadir',
                label: 'Hadir',
                color: '16, 185, 129'
            },
            {
                key: 'terlambat',
                label: 'Terlambat',
                color: '245, 158, 11'
            },
            {
                key: 'izin',
                label: 'Izin',
                color: '59, 130, 246'
            },
            {
                key: 'sakit',
                label: 'Sakit',
                color: '139, 92, 246'
            },
            {
                key: 'alpa',
                label: 'Alpa',
                color: '244, 63, 94'
            },
        ];

        let total = 0;
        const datasets = series.map(function(s) {
            const values = data.datasets[s.key] || [];
            total += values.reduce(function(a, b) {
                return a + b;
            }, 0);
            return {
                label: s.label,
                data: values,
                backgroundColor: 'rgba(' + s.color + ', 0.85)',
                borderRadius: 3,
            };
        });

        if (total === 0) empty.classList.remove('hidden');

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 8,
                            font: {
                                size: 11,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                return items.length ? 'Tanggal ' + items[0].label : '';
                            },
                            footer: function(items) {
                                let sum = 0;
                                items.forEach(function(i) {
                                    sum += i.parsed.y;
                                });
                                return 'Total Sesi: ' + sum;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    } catch (err) {
        console.error(err);
        empty.classList.remove('hidden');
        empty.innerHTML = '<p class="text-sm font-bold text-slate-700">Gagal memuat grafik</p>';
    } finally {
        loading.classList.add('hidden');
    }
});
</script>
@endpush