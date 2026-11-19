<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Panggilan Orang Tua - {{ $summon->letter_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #f8fafc;
            margin: 0;
            padding: 20px 0;
            font-size: 12pt;
            line-height: 1.5;
        }
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm 25mm;
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .header-kop h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-kop h2 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-kop h1 {
            margin: 2px 0;
            font-size: 17pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .header-kop p {
            margin: 2px 0;
            font-size: 9.5pt;
        }
        .letter-meta {
            margin-bottom: 18px;
        }
        .letter-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .letter-meta td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 11pt;
        }
        .recipient {
            margin-bottom: 18px;
            font-size: 11.5pt;
        }
        .content-body {
            text-align: justify;
            font-size: 11.5pt;
            margin-bottom: 15px;
        }
        .content-table {
            width: 90%;
            margin: 12px auto 18px auto;
            border-collapse: collapse;
        }
        .content-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11pt;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 11pt;
            padding: 5px;
        }
        .sign-space {
            height: 65px;
        }
        .no-print-bar {
            background: #1e293b;
            color: #fff;
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: sans-serif;
            font-size: 13px;
        }
        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-back {
            background: #475569;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print-bar { display: none !important; }
            .page { box-shadow: none; padding: 10mm 15mm; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div>
            <strong>Surat Panggilan Orang Tua Siap Cetak</strong> &bull; No: {{ $summon->letter_number }}
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('kedisiplinan.summons.index') }}" class="btn-back">&larr; Kembali ke Daftar</a>
            <button onclick="window.print()" class="btn-print">&#128438; Cetak Dokumen / Simpan PDF</button>
        </div>
    </div>

    <div class="page">
        <!-- Kop Surat Resmi -->
        <div class="header-kop">
            <h3>Pemerintah Daerah Provinsi Jawa Barat</h3>
            <h2>Dinas Pendidikan</h2>
            <h2>Cabang Dinas Pendidikan Wilayah IV</h2>
            <h1>SMA NEGERI 5 KARAWANG</h1>
            <p>{{ $settings->school_address ?? 'Jl. Jend. A. Yani No. 10, Karawang Kulon, Karawang, Jawa Barat' }}</p>
            <p>Telp: {{ $settings->school_phone ?? '(0267) 402345' }} &bull; Website: www.sman5karawang.sch.id &bull; Email: {{ $settings->school_email ?? 'info@sman5karawang.sch.id' }}</p>
        </div>

        <!-- Meta Surat -->
        <div class="letter-meta">
            <table>
                <tr>
                    <td style="width: 15%;">Nomor</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 48%;"><strong>{{ $summon->letter_number }}</strong></td>
                    <td style="width: 35%; text-align: right;">Karawang, {{ \Carbon\Carbon::parse($summon->created_at)->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td>Lampiran</td>
                    <td>:</td>
                    <td>-</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Perihal</td>
                    <td>:</td>
                    <td><strong><u>SURAT PANGGILAN ORANG TUA / WALI</u></strong></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- Penerima -->
        <div class="recipient">
            Kepada Yth.<br>
            <strong>{{ $summon->recipient_parent_name ?: 'Bapak / Ibu Orang Tua / Wali Siswa' }}</strong><br>
            dari <strong>{{ $summon->student->name }}</strong><br>
            di Tempat
        </div>

        <!-- Isi Surat -->
        <div class="content-body">
            <p>Dengan hormat,</p>
            <p style="text-indent: 30px;">
                Sehubungan dengan pembinaan kedisiplinan dan evaluasi perkembangan peserta didik di lingkungan SMA Negeri 5 Karawang, maka dengan ini pihak sekolah mengharapkan kehadiran Bapak/Ibu Orang Tua/Wali dari siswa:
            </p>

            <table class="content-table">
                <tr>
                    <td style="width: 30%;">Nama Siswa</td>
                    <td style="width: 3%;">:</td>
                    <td><strong>{{ $summon->student->name }}</strong></td>
                </tr>
                <tr>
                    <td>NIS / NISN</td>
                    <td>:</td>
                    <td>{{ $summon->student->nis }} / {{ $summon->student->nisn ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>:</td>
                    <td>{{ $summon->student->schoolClass->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Wali Kelas</td>
                    <td>:</td>
                    <td>{{ $summon->homeroom_teacher_name ?: ($summon->student->schoolClass->homeroomTeacher->name ?? '-') }}</td>
                </tr>
                <tr>
                    <td>Alasan Pemanggilan</td>
                    <td>:</td>
                    <td>{{ $summon->reason }}</td>
                </tr>
            </table>

            <p style="text-indent: 30px;">Untuk hadir dalam pertemuan konsultasi dan pembinaan yang dijadwalkan pada:</p>

            <table class="content-table" style="background: #fafafa; border: 1px solid #eee;">
                <tr>
                    <td style="width: 30%;">Hari / Tanggal</td>
                    <td style="width: 3%;">:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($summon->meeting_date)->translatedFormat('l, d F Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td><strong>Pukul {{ substr($summon->meeting_time, 0, 5) }} WIB s.d. Selesai</strong></td>
                </tr>
                <tr>
                    <td>Tempat</td>
                    <td>:</td>
                    <td>{{ $summon->meeting_place }}</td>
                </tr>
                <tr>
                    <td>Bertemu Dengan</td>
                    <td>:</td>
                    <td>Guru BP/BK / Tim Kedisiplinan & Wali Kelas</td>
                </tr>
            </table>

            <p style="text-indent: 30px;">
                Mengingat sangat pentingnya hal tersebut demi kebaikan dan masa depan pendidikan putra/putri Bapak/Ibu, kehadiran tepat waktu sangat kami harapkan.
            </p>
            <p style="text-indent: 30px;">
                Demikian surat panggilan ini kami sampaikan. Atas perhatian, kehadiran, dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.
            </p>
        </div>

        <!-- Kolom Tanda Tangan Formal -->
        <table class="signatures">
            <tr>
                <td>
                    Mengetahui,<br>
                    Wali Kelas,
                    <div class="sign-space"></div>
                    <strong><u>{{ $summon->homeroom_teacher_name ?: ($summon->student->schoolClass->homeroomTeacher->name ?? 'Wali Kelas') }}</u></strong><br>
                    NIP. {{ $summon->student->schoolClass->homeroomTeacher->nip ?? '-' }}
                </td>
                <td>
                    {{ $summon->acknowledged_by_title }},
                    <div class="sign-space"></div>
                    <strong><u>{{ $summon->acknowledged_by_name }}</u></strong><br>
                    NIP. {{ $summon->acknowledged_by_nip ?: '-' }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 30px;">
                    Mengetahui,<br>
                    Kepala SMA Negeri 5 Karawang,
                    <div class="sign-space"></div>
                    <strong><u>{{ $settings->headmaster_name ?? 'H. Suhaerudin, M.Pd.' }}</u></strong><br>
                    NIP. {{ $settings->headmaster_nip ?? '19680512 199403 1 004' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
