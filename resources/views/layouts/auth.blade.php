<!DOCTYPE html>
<html lang="id" class="bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/img/LOGO_SMANLI.png">
    <title>@yield('title', 'Login') - SMAN 5 Karawang</title>
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
    </style>
</head>

<body
    class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
    <div class="flex-1 flex flex-col justify-center py-6 sm:py-10 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div
                class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-500 text-white shadow-lg shadow-blue-500/30 mb-3 sm:mb-4">
                <img src="/img/LOGO_SMANLI.png" style="width: 80%">
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">SMAN 5 KARAWANG</h1>
            <p class="text-[11px] sm:text-xs font-semibold tracking-wider uppercase text-blue-600 mt-1">Sistem Absensi
                Guru & Siswa Terintegrasi</p>
        </div>

        <div class="mt-5 px-4 sm:mt-6 sm:mx-auto sm:w-full sm:max-w-md">
            @if(session('success'))
            <div
                class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 flex items-start space-x-3">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-base"></i>
                <div>{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div
                class="mb-4 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 flex items-start space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5 text-base"></i>
                <div>{{ session('error') }}</div>
            </div>
            @endif

            @if(session('warning'))
            <div
                class="mb-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-start space-x-3">
                <i class="fa-solid fa-circle-exclamation text-amber-500 mt-0.5 text-base"></i>
                <div>{{ session('warning') }}</div>
            </div>
            @endif

            @if(session('info'))
            <div
                class="mb-4 rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800 flex items-start space-x-3">
                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 text-base"></i>
                <div>{{ session('info') }}</div>
            </div>
            @endif

            <div
                class="bg-white py-6 sm:py-8 px-4 sm:px-8 shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-100">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="py-4 sm:py-6 px-4 text-center text-xs text-slate-400 border-t border-slate-200/60 mt-auto">
        &copy; {{ date('Y') }} SMAN 5 Karawang. Hak cipta dilindungi undang-undang.
    </footer>
</body>

</html>