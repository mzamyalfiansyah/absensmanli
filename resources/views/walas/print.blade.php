<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi Siswa Kelas {{ $class->name ?? 'Semua Kelas' }}</title>
    <style>
    @page {
        size: A4 landscape;
        margin: 15mm;
    }

    body {
        font-family: 'Times New Roman', Times, serif;
        color: #000;
        background: #fff;
        margin: 0;
        padding: 20px;
        font-size: 10pt;
    }

    .header-kop {
        text-align: center;
        border-bottom: 3px double #000;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }

    .header-kop h3 {
        margin: 0;
        font-size: 11pt;
        text-transform: uppercase;
        font-weight: normal;
    }

    .header-kop h2 {
        margin: 2px 0;
        font-size: 13pt;
        text-transform: uppercase;
        font-weight: bold;
    }

    .header-kop h1 {
        margin: 2px 0;
        font-size: 15pt;
        text-transform: uppercase;
        font-weight: bold;
    }

    .header-kop p {
        margin: 2px 0;
        font-size: 8.5pt;
    }

    .title {
        text-align: center;
        margin-bottom: 15px;
    }

    .title h4 {
        margin: 0;
        font-size: 12pt;
        text-transform: uppercase;
    }

    .title p {
        margin: 2px 0;
        font-size: 9.5pt;
        color: #333;
    }

    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table.data-table th,
    table.data-table td {
        border: 1px solid #333;
        padding: 5px 8px;
        font-size: 9pt;
    }

    table.data-table th {
        background: #f0f0f0;
        text-align: center;
        font-weight: bold;
    }

    .signatures {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }

    .signatures td {
        width: 50%;
        text-align: center;
        font-size: 10pt;
        vertical-align: top;
    }

    .sign-space {
        height: 60px;
    }

    .no-print {
        background: #1e293b;
        color: #fff;
        padding: 10px 20px;
        position: sticky;
        top: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: sans-serif;
        font-size: 12px;
        margin: -20px -20px 20px -20px;
    }

    .btn-print {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
    </style>
</head>

<body>
    <div class="no-print">
        <span>Rekapitulasi Presensi Kelas {{ $class->name ?? 'Semua Kelas' }} Siap Cetak</span>
        <button onclick="window.print()" class="btn-print">&#128438; Cetak / Simpan PDF</button>
    </div>

    <div class="header-kop">
        <h3>Pemerintah Daerah Provinsi Jawa Barat - Dinas Pendidikan</h3>
        <h2>Cabang Dinas Pendidikan Wilayah IV</h2>
        <h1>SMA NEGERI 5 KARAWANG</h1>
        <p>Jl. Jend. A. Yani No. 10, Karawang Kulon, Karawang, Jawa Barat &bull; Telp: (0267) 402345</p>
    </div>

    <div class="title">
        <h4>Laporan Rekapitulasi Presensi Kelas {{ $class->name ?? 'Semua Kelas' }}</h4>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s.d.
            {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 12%;">NIS</th>
                <th>Nama Siswa</th>
                <th>Mata Pelajaran</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Waktu Scan</th>
                <th style="width: 12%;">Validasi Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $idx => $row)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td style="text-align: center;">{{ $row->date->format('d/m/Y') }}</td>
                <td style="font-family: monospace; text-align: center;">{{ $row->student->nis ?? '-' }}</td>
                <td><strong>{{ $row->student->name ?? '-' }}</strong></td>
                <td>{{ $row->schedule->subject->name ?? '-' }}</td>
                <td style="text-align: center; text-transform: capitalize;">{{ $row->status }}</td>
                <td style="text-align: center;">{{ $row->scanned_at ? $row->scanned_at->format('H:i') . ' WIB' : '-' }}
                </td>
                <td style="text-align: center;">{{ $row->location_status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">
                    Tidak ada catatan presensi pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala SMA Negeri 5 Karawang,
                <div class="sign-space"></div>
                <strong><u>H. Suhaerudin, M.Pd.</u></strong><br>
                NIP. 19680512 199403 1 004
            </td>
            <td>
                Karawang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Wali Kelas {{ $class->name ?? 'Semua Kelas'}},
                <div class="sign-space"></div>
                <strong><u>{{ $class->homeroomTeacher->name ?? 'Wali Kelas' }}</u></strong><br>
                NIP. {{ $class->homeroomTeacher->nip ?? '-' }}
            </td>
        </tr>
    </table>
</body>

</html>