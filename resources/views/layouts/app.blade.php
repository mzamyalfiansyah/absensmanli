<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/img/LOGO_SMANLI.png">
    <title>@yield('title', 'Sistem Absensi') - SMAN 5 Karawang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        body {
            background: white !important;
        }
    }
    </style>
    @stack('styles')
</head>

<body class="h-full flex text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()"
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs hidden lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed top-0 bottom-0 left-0 z-50 w-72 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0 no-print">
        <!-- Brand -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 font-bold text-lg">
                    <img src="/img/LOGO_SMANLI.png" style="width: 80%">
                </div>
                <div>
                    <div class=" font-extrabold text-slate-900 tracking-tight text-sm leading-tight">SMAN 5 KARAWANG
                    </div>
                    <div class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Absensi & Disiplin
                    </div>
                </div>
            </a>
            <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- User Role Badge -->
        <div class="px-6 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2 truncate">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span
                    class="text-xs font-semibold text-slate-700 truncate">{{ auth()->user()->role_display_name }}</span>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 uppercase">WIB</span>
        </div>

        <!-- Tahun Ajaran (tampil di semua role) -->
        @php $tahunAjaranOpen = request()->routeIs('tahun_ajaran.*'); @endphp
        <div class="px-4 pt-3">
            <button type="button" onclick="toggleTahunAjaran()"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                <span class="flex items-center">
                    <i class="fa-solid fa-calendar-days w-6 text-center mr-2 text-blue-600"></i>
                    Tahun Ajaran: {{ \App\Models\AcademicYear::getActive()?->name ?? '2025/2026' }}
                </span>
                <i id="tahunAjaranIcon"
                    class="fa-solid {{ $tahunAjaranOpen ? 'fa-chevron-up' : 'fa-chevron-down' }} text-[10px]"></i>
            </button>

            <div id="tahunAjaranMenu"
                class="{{ $tahunAjaranOpen ? '' : 'hidden' }} mt-2 rounded-xl border border-slate-200 overflow-hidden"
                style="max-height:16rem;overflow-y:auto;">
                @foreach(\App\Models\AcademicYear::yearOptions() as $opt)
                <a href="{{ route('tahun_ajaran.show', $opt['slug']) }}"
                    class="flex items-center justify-between px-4 py-2.5 text-xs transition hover:bg-slate-50 {{ request()->is('tahun-ajaran/' . $opt['slug']) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600' }}">
                    <span>{{ $opt['label'] }}</span>
                    @if($opt['status'] === 'aktif')
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold">Aktif</span>
                    @elseif($opt['status'] === 'mendatang')
                    <span class="text-slate-400">Mendatang</span>
                    @endif
                </a>
                @endforeach

                <a href="{{ route('tahun_ajaran.index') }}"
                    class="flex items-center justify-center px-4 py-3 text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                    <i class="fa-solid fa-layer-group mr-2"></i> Lihat Semua
                </a>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1 text-sm font-medium">
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-semibold shadow-md shadow-blue-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <i class="fa-solid fa-gauge-high w-6 text-center text-base mr-2"></i>
                <span>Dashboard</span>
            </a>

            <!-- SUPERADMIN NAVIGATION -->
            @if(auth()->user()->isSuperAdmin())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Super
                Administrator</div>
            <a href="{{ route('admin.users.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-users-gear w-6 text-center mr-2"></i>
                <span>Kelola Akun & Role</span>
            </a>
            <a href="{{ route('admin.master.academic_years') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.master.academic_years*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-calendar-days w-6 text-center mr-2"></i>
                <span>Tahun Ajaran</span>
            </a>
            <a href="{{ route('admin.master.classes') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.master.classes*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chalkboard-user w-6 text-center mr-2"></i>
                <span>Data Kelas & Walas</span>
            </a>
            <a href="{{ route('admin.master.teachers') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.master.teachers*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-user-tie w-6 text-center mr-2"></i>
                <span>Data Guru & Import</span>
            </a>
            <a href="{{ route('admin.master.students') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.master.students*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-user-graduate w-6 text-center mr-2"></i>
                <span>Data Siswa & Import</span>
            </a>
            <a href="{{ route('admin.master.subjects') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.master.subjects*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-book-bookmark w-6 text-center mr-2"></i>
                <span>Mata Pelajaran</span>
            </a>
            <a href="{{ route('admin.master.schedules') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.master.schedules*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clock w-6 text-center mr-2"></i>
                <span>Jadwal Pelajaran</span>
            </a>
            <a href="{{ route('admin.settings') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.settings*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-location-dot w-6 text-center mr-2 text-rose-500"></i>
                <span>Koordinat & Radius GPS</span>
            </a>
            <a href="{{ route('admin.audit_logs') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.audit_logs*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-shield-virus w-6 text-center mr-2"></i>
                <span>Log Aktivitas & Audit</span>
            </a>
            @endif

            <!-- ADMIN KEDISIPLINAN -->
            @if(auth()->user()->isAdminKedisiplinan())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kedisiplinan & BK
            </div>
            <a href="{{ route('kedisiplinan.types.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kedisiplinan.types.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-tags w-6 text-center mr-2"></i>
                <span>Jenis Pelanggaran</span>
            </a>
            <a href="{{ route('kedisiplinan.violations.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kedisiplinan.violations.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-triangle-exclamation w-6 text-center mr-2 text-amber-500"></i>
                <span>Catat Pelanggaran</span>
            </a>
            <a href="{{ route('kedisiplinan.summons.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kedisiplinan.summons.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-envelope-open-text w-6 text-center mr-2 text-blue-600"></i>
                <span>Surat Panggilan Ortu</span>
            </a>
            @endif

            <!-- ADMIN KURIKULUM -->
            @if(auth()->user()->isAdminKurikulum())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kurikulum & KBM
            </div>
            <a href="{{ route('kurikulum.teacher_monitor') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.teacher_monitor*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-user-check w-6 text-center mr-2 text-emerald-600"></i>
                <span>Monitoring Guru</span>
            </a>
            <a href="{{ route('kurikulum.schedules.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.schedules*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clock w-6 text-center mr-2 text-blue-600"></i>
                <span>Jadwal Pelajaran</span>
            </a>
            <a href="{{ route('kurikulum.class_reports.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.class_reports.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clipboard-check w-6 text-center mr-2 text-indigo-600"></i>
                <span>Verifikasi Laporan Kelas</span>
            </a>
            <a href="{{ route('kurikulum.recaps.student') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.recaps.student*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clipboard-user w-6 text-center mr-2"></i>
                <span>Log Detail Presensi</span>
            </a>
            <a href="{{ route('kurikulum.recaps.monthly') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.recaps.monthly*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-table-list w-6 text-center mr-2 text-indigo-600"></i>
                <span>Rekap Absensi Bulanan</span>
            </a>
            <a href="{{ route('kurikulum.recaps.chart') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.recaps.chart*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chart-column w-6 text-center mr-2 text-emerald-600"></i>
                <span>Grafik Absensi Bulanan</span>
            </a>
            <a href="{{ route('kurikulum.recaps.teacher') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('kurikulum.recaps.teacher*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-file-invoice w-6 text-center mr-2"></i>
                <span>Rekap Absensi Guru</span>
            </a>
            @endif

            <!-- ADMIN WALAS -->
            @if(auth()->user()->isAdminWalas())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Wali Kelas</div>
            <a href="{{ route('walas.attendance') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('walas.attendance*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-users w-6 text-center mr-2"></i>
                <span>Absensi Perwalian</span>
            </a>

            <a href="{{ route('walas.class_reports') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('walas.class_reports*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clipboard-list w-6 text-center mr-2 text-indigo-600"></i>
                <span>Laporan Admin Kelas</span>
            </a>

            <a href="{{ route('walas.violations') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('walas.violations*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-user-shield w-6 text-center mr-2"></i>
                <span>Pelanggaran Siswa</span>
            </a>
            @endif

            <!-- ADMIN KELAS -->
            @if(auth()->user()->isAdminKelas())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Admin Kelas</div>
            <a href="{{ route('admin_kelas.index') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('admin_kelas.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-list-check w-6 text-center mr-2"></i>
                <span>Lapor Kondisi & Guru</span>
            </a>
            @endif

            <!-- GURU -->
            @if(auth()->user()->isGuru() || auth()->user()->isSuperAdmin())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Guru Pengajar
            </div>
            <a href="{{ route('guru.schedules') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('guru.schedules*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-calendar-check w-6 text-center mr-2 text-blue-600"></i>
                <span>Jadwal & Buka Sesi QR</span>
            </a>
            <a href="{{ route('guru.history') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('guru.history*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-clock-rotate-left w-6 text-center mr-2"></i>
                <span>Riwayat Mengajar</span>
            </a>
            @endif

            <!-- SISWA -->
            @if(auth()->user()->isSiswa() || auth()->user()->isSuperAdmin())
            <div class="pt-4 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa</div>
            <a href="{{ route('siswa.scan') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('siswa.scan*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-qrcode w-6 text-center mr-2 text-indigo-600"></i>
                <span>Scan QR Absensi</span>
            </a>
            <a href="{{ route('siswa.history') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('siswa.history*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-history w-6 text-center mr-2"></i>
                <span>Riwayat Absensi</span>
            </a>
            <a href="{{ route('siswa.violations') }}"
                class="flex items-center px-3 py-2 rounded-xl transition {{ request()->routeIs('siswa.violations*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fa-solid fa-shield-halved w-6 text-center mr-2"></i>
                <span>Catatan Kedisiplinan</span>
            </a>
            @endif
        </nav>

        <!-- Bottom User Profile Pill -->
        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <a href="{{ route('profile') }}" class="flex items-center space-x-3 overflow-hidden group">
                    <div
                        class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition truncate">
                            {{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-500 truncate">{{ auth()->user()->username }}</div>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-rose-600 transition">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 min-w-0 flex flex-col lg:pl-72 min-h-screen">
        <!-- Top Navigation Bar -->
        <header
            class="h-16 sm:h-20 bg-white border-b border-slate-200 px-3 sm:px-8 flex items-center justify-between sticky top-0 z-30 no-print">
            <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1 mr-2">
                <button onclick="toggleSidebar()" aria-label="Menu"
                    class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 shrink-0">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-lg md:text-xl font-bold text-slate-900 tracking-tight truncate">
                        @yield('page_title', 'Dashboard')</h1>
                    <div class="text-[11px] sm:text-xs text-slate-500 hidden sm:block truncate">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} &bull; SMAN 5 Karawang
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-1.5 sm:space-x-3 shrink-0">
                <div
                    class="hidden md:flex items-center px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium space-x-2">
                    <i class="fa-solid fa-circle-dot text-emerald-500 text-[10px]"></i>
                    <span>Tahun Ajaran: {{ \App\Models\AcademicYear::getActive()?->name ?? '2025/2026' }}</span>
                </div>

                <a href="{{ route('profile') }}"
                    class="p-2 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-xl transition"
                    title="Profil">
                    <i class="fa-regular fa-circle-user text-lg sm:text-xl"></i>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Keluar"
                        class="inline-flex items-center px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition">
                        <i class="fa-solid fa-arrow-right-from-bracket sm:mr-1.5"></i>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto ">
            <!-- Flash Notifications -->
            @if(session('success'))
            <div
                class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-start space-x-3 shadow-xs no-print">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div
                class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-start space-x-3 shadow-xs no-print">
                <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
            @endif

            @if(session('warning'))
            <div
                class="mb-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-start space-x-3 shadow-xs no-print">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('warning') }}</div>
            </div>
            @endif

            @if(session('info'))
            <div
                class="mb-6 rounded-2xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800 flex items-start space-x-3 shadow-xs no-print">
                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 text-lg"></i>
                <div class="flex-1 font-medium">{{ session('info') }}</div>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer
            class="py-4 sm:py-6 px-4 sm:px-8 border-t border-slate-200/60 text-center text-xs text-slate-400 no-print mt-auto">
            &copy; {{ date('Y') }} SMAN 5 Karawang. Hak cipta dilindungi undang-undang.
        </footer>
    </div>

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }
    }

    function toggleTahunAjaran() {
        document.getElementById('tahunAjaranMenu').classList.toggle('hidden');

        var icon = document.getElementById('tahunAjaranIcon');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
    }
    </script>
    @stack('scripts')
</body>

</html>