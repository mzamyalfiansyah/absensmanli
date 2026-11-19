@extends('layouts.app')

@section('title', 'Scan QR Absensi')
@section('page_title', 'Pindai QR Code Absensi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Status GPS & Lokasi Geofencing Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center">
                <i class="fa-solid fa-location-crosshairs mr-2 text-rose-500"></i> Validasi Lokasi Presensi (Geofencing)
            </h3>
            <span id="geoStatusBadge" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                Mendeteksi GPS...
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-100">
            <div>
                <span class="text-slate-400 block text-[10px]">Titik Radius Sekolah</span>
                <span class="font-semibold text-slate-800">{{ $settings->allowed_radius_meters }} Meter</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px]">Akurasi GPS Anda</span>
                <span id="geoAccuracyDisplay" class="font-semibold text-slate-800">-</span>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <span class="text-slate-400 block text-[10px]">Jarak ke Titik Sekolah</span>
                <span id="geoDistanceDisplay" class="font-semibold text-slate-800">-</span>
            </div>
        </div>

        <div id="geoWarningText" class="text-xs text-slate-500 flex items-start space-x-2">
            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
            <span id="geoMessage">Meminta izin akses lokasi perangkat untuk validasi server-side...</span>
        </div>
    </div>

    <!-- Scanner Box -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs text-center space-y-4">
        <div class="max-w-md mx-auto">
            <div id="reader" class="overflow-hidden rounded-2xl border-2 border-dashed border-indigo-200 bg-slate-900 aspect-square flex items-center justify-center text-white relative">
                <div class="text-xs text-slate-400 p-4">
                    <i class="fa-solid fa-camera text-3xl mb-2 text-indigo-400 animate-pulse"></i>
                    <div>Klik tombol "Mulai Kamera" di bawah untuk memindai QR Code sesi absensi guru.</div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
            <button type="button" id="startScanBtn" onclick="startCamera()"
                class="w-full sm:w-auto px-5 py-3 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center">
                <i class="fa-solid fa-camera mr-2"></i> Mulai Kamera
            </button>
            <button type="button" id="stopScanBtn" onclick="stopCamera()" disabled
                class="w-full sm:w-auto px-5 py-3 sm:py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs cursor-not-allowed transition flex items-center justify-center">
                <i class="fa-solid fa-stop mr-2"></i> Hentikan Kamera
            </button>
        </div>

        <!-- Feedback Result Message Box -->
        <div id="scanResultCard" class="hidden p-4 rounded-xl text-xs font-semibold text-left transition"></div>
    </div>

    <!-- Manual Token Input Fallback -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs">
        <button type="button" onclick="document.getElementById('manualInputContainer').classList.toggle('hidden')"
            class="w-full flex items-center justify-between text-xs font-bold text-slate-700 hover:text-blue-600">
            <span><i class="fa-solid fa-keyboard mr-2 text-slate-400"></i> Kamera bermasalah? Masukkan Token QR Manual</span>
            <i class="fa-solid fa-chevron-down text-slate-400 text-[10px]"></i>
        </button>

        <div id="manualInputContainer" class="hidden mt-4 pt-3 border-t border-slate-100 space-y-3">
            <p class="text-xs text-slate-500">Jika kamera smartphone Anda tidak didukung, mintalah kode sesi pada guru Anda dan masukkan di sini:</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" id="manualTokenInput" placeholder="Contoh: SMANLI-ATT-..."
                    class="w-full sm:flex-1 rounded-xl border border-slate-300 px-3 py-2.5 text-xs font-mono uppercase focus:border-blue-600 focus:outline-none">
                <button type="button" onclick="submitManualToken()"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                    Kirim Absensi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Load HTML5-QRCode Scanner Library -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
let html5QrCode = null;
let currentCoords = { latitude: null, longitude: null, accuracy: null };
const schoolLat = {{ $settings->school_latitude }};
const schoolLon = {{ $settings->school_longitude }};
const allowedRadius = {{ $settings->allowed_radius_meters }};

// Calculate Haversine in JS for instant UI feedback (validated on server as well!)
function calculateDistanceJs(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return Math.round(R * c);
}

function initGeolocation() {
    if (!navigator.geolocation) {
        updateGeoUI(false, "Browser tidak mendukung GPS.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            currentCoords.latitude = pos.coords.latitude;
            currentCoords.longitude = pos.coords.longitude;
            currentCoords.accuracy = Math.round(pos.coords.accuracy);

            const distance = calculateDistanceJs(currentCoords.latitude, currentCoords.longitude, schoolLat, schoolLon);
            document.getElementById('geoAccuracyDisplay').innerText = "±" + currentCoords.accuracy + " m";
            document.getElementById('geoDistanceDisplay').innerText = distance + " m";

            if (distance <= allowedRadius) {
                updateGeoUI(true, `Lokasi Valid (${distance}m dari sekolah, radius ${allowedRadius}m).`);
            } else {
                updateGeoUI(false, `Di luar radius (${distance}m > ${allowedRadius}m). Absensi mungkin ditolak.`);
            }
        },
        (err) => {
            console.warn("GPS error:", err.message);
            updateGeoUI(false, "Izin lokasi tidak diberikan. " + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function updateGeoUI(isValid, message) {
    const badge = document.getElementById('geoStatusBadge');
    const msg = document.getElementById('geoMessage');
    msg.innerText = message;

    if (isValid) {
        badge.className = "px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800";
        badge.innerText = "Lokasi Valid";
    } else {
        badge.className = "px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800";
        badge.innerText = "Luar Radius / GPS Lemah";
    }
}

function startCamera() {
    initGeolocation();
    const qrRegion = document.getElementById('reader');
    html5QrCode = new Html5Qrcode("reader");

    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
        // QR detected! Stop camera and send
        stopCamera();
        processScan(decodedText);
    }).then(() => {
        document.getElementById('startScanBtn').disabled = true;
        document.getElementById('startScanBtn').className = "px-5 py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs cursor-not-allowed transition flex items-center";
        document.getElementById('stopScanBtn').disabled = false;
        document.getElementById('stopScanBtn').className = "px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition flex items-center";
    }).catch(err => {
        alert("Gagal mengakses kamera: " + err);
    });
}

function stopCamera() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            document.getElementById('startScanBtn').disabled = false;
            document.getElementById('startScanBtn').className = "px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center";
            document.getElementById('stopScanBtn').disabled = true;
            document.getElementById('stopScanBtn').className = "px-5 py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs cursor-not-allowed transition flex items-center";
        }).catch(err => console.error(err));
    }
}

function submitManualToken() {
    const token = document.getElementById('manualTokenInput').value.trim();
    if (!token) {
        alert('Masukkan kode token QR.');
        return;
    }
    processScan(token);
}

function processScan(token) {
    const card = document.getElementById('scanResultCard');
    card.classList.remove('hidden');
    card.className = "p-4 rounded-xl text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200 block";
    card.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengirimkan data presensi & memverifikasi lokasi server...`;

    fetch("{{ route('siswa.scan.submit') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            "Accept": "application/json"
        },
        body: JSON.stringify({
            qr_token: token,
            latitude: currentCoords.latitude,
            longitude: currentCoords.longitude,
            accuracy: currentCoords.accuracy
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            card.className = "p-4 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 block";
            card.innerHTML = `
                <div class="flex items-center space-x-2 font-bold text-emerald-900 text-sm mb-1">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>${data.message}</span>
                </div>
                <div class="text-[11px] text-emerald-700 mt-1">Presensi Anda telah terekam di sistem server SMAN 5 Karawang.</div>
                <a href="{{ route('siswa.history') }}" class="inline-block mt-3 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px]">Lihat Riwayat Presensi &rarr;</a>
            `;
        } else {
            card.className = "p-4 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 block";
            card.innerHTML = `
                <div class="flex items-center space-x-2 font-bold text-rose-900 text-sm mb-1">
                    <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                    <span>Presensi Gagal</span>
                </div>
                <div>${data.message}</div>
            `;
        }
    })
    .catch(err => {
        card.className = "p-4 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 block";
        card.innerText = "Terjadi kesalahan koneksi server: " + err;
    });
}

document.addEventListener('DOMContentLoaded', initGeolocation);
</script>
@endsection
