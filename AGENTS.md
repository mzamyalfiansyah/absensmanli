# AGENTS.md - Panduan Pengembangan & Arsitektur SMANLI Absensi

Dokumen ini adalah instruksi acuan utama (rulebook & guidelines) bagi AI Agent maupun developer yang berkontribusi pada pengembangan sistem **SMAN 5 Karawang - Sistem Informasi Absensi & Kedisiplinan Siswa / Guru (SMANLI)**.

---

## 1. Ringkasan Proyek & Tech Stack

Aplikasi ini merupakan sistem informasi terintegrasi untuk manajemen presensi berbasis QR code dinamis, geolokasi sekolah, pemantauan kondisi kelas harian, dan tata tertib/kedisiplinan siswa di SMAN 5 Karawang.

### Tech Stack Utama
- **Backend Framework**: Laravel 13 (`laravel/framework: ^13.17`)
- **PHP**: PHP 8.3+ (Environment target: PHP 8.5)
- **Database**: 
  - Production / Local Dev: MySQL (MariaDB Laragon)
  - Testing / CI: SQLite in-memory (`:memory:`)
- **Frontend & Styling**: Blade Templating + Tailwind CSS v4 (`@tailwindcss/vite`, `vite: ^8.0.0`)
- **QR Code Engine**: `chillerlan/php-qrcode` (Menghasilkan dynamic SVG & Base64 Data URI)
- **Code Quality & Testing**: Laravel Pint (`laravel/pint`), PHPUnit 12 (`phpunit/phpunit: ^12.5`), Laravel Pail

---

## 2. Struktur Direktori & Arsitektur

Arsitektur aplikasi memadukan pola **MVC + Service Layer**:

```
Projek-absensi-smanli/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                  # Controller administrasi khusus (e.g. ScheduleController)
│   │   │   ├── AdminKedisiplinanController.php
│   │   │   ├── AdminKelasController.php
│   │   │   ├── AdminKurikulumController.php
│   │   │   ├── AdminWalasController.php
│   │   │   ├── AttendanceAttachmentController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── GuruController.php
│   │   │   ├── SiswaController.php
│   │   │   ├── SuperAdminController.php
│   │   │   └── TahunAjaranController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php            # Aliased sebagai 'role'
│   │       ├── EnsureAccountActive.php  # Aliased sebagai 'account.active'
│   │       └── EnsurePasswordChanged.php# Aliased sebagai 'must.change.password'
│   ├── Models/                          # 16 Model Eloquent
│   └── Services/
│       ├── CsvService.php               # Parsing CSV, import massal siswa/guru, export rekap
│       ├── GeoLocationService.php       # Kalkulasi jarak Haversine & validasi radius presensi
│       └── QrCodeService.php            # Generator SVG/Base64 QR Code
├── database/
│   ├── migrations/                      # Skema tabel inti sekolah & absensi
│   └── seeders/                         # Seeder data awal, admin, dan pengaturan
├── resources/
│   ├── css/app.css                      # Tailwind CSS v4 configuration
│   ├── js/                              # Script kamera scanner QR & geolocation navigator
│   └── views/                           # Blade templates per role
├── routes/
│   └── web.php                          # Definisi routing aplikasi
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 3. Matriks Peran & Hak Akses (RBAC)

Aplikasi memiliki 7 tingkatan peran pengguna (`role`) yang diatur secara ketat melalui middleware `role:<nama_role>` dan method helper pada [`User`](app/Models/User.php):

| Role | Kode Role | Cakupan Fitur & Tanggung Jawab |
| :--- | :--- | :--- |
| **Super Administrator** | `superadmin` | Akses penuh: Master data (Tahun Ajaran, Kelas, Guru, Siswa, Mapel, Jadwal), Manajemen User & Akun, Pengaturan Radius & Koordinat Sistem, Audit Logs. |
| **Admin Kedisiplinan** | `admin_kedisiplinan` | Master jenis pelanggaran, pencatatan pelanggaran siswa, pembuatan dan pencetakan Surat Pemanggilan Orang Tua (Parent Summon), export data pelanggaran. |
| **Admin Kurikulum** | `admin_kurikulum` | Monitoring presensi guru real-time, approval/rejection laporan kondisi kelas, rekapitulasi kehadiran guru & siswa, jadwal pelajaran, cetak & export CSV rekapitulasi. |
| **Wali Kelas** | `admin_walas` | Rekap kehadiran siswa kelas perwalian, laporan pelanggaran siswa binaan, monitoring laporan kondisi kelas harian, cetak laporan perwalian. |
| **Admin Kelas** | `admin_kelas` | Siswa pengurus kelas yang berhak melaporkan kondisi KBM di kelas (status kedatangan guru, ada/tidaknya tugas, kendala kelas) dan absensi siswa sakit/izin. |
| **Guru** | `guru` | Jadwal mengajar, pembukaan/penutupan sesi QR absensi dinamis per jam pelajaran, absensi manual siswa, self check-in kehadiran guru, riwayat presensi. |
| **Siswa** | `siswa` | Pemindaian (scan) QR code presensi mata pelajaran, riwayat kehadiran pribadi, daftar catatan poin pelanggaran kedisiplinan. |

> **Aturan Otorisasi**:
> - `superadmin` memiliki hak untuk mengakses rute level staf (`admin_kedisiplinan`, `admin_kurikulum`, `admin_walas`) secara otomatis.
> - Setiap pengguna yang dinonaktifkan (`is_active = false`) wajib ditolak oleh middleware `EnsureAccountActive`.
> - Setiap pengguna dengan bendera `must_change_password = true` wajib dialihkan ke halaman ubah password sebelum dapat mengakses fitur lain.

---

## 4. Alur Kerja Inti (Core Business Workflows)

### 4.1. Presensi Siswa via Dynamic QR & Geolocation
1. **Guru Membuka Sesi**: Guru membuka sesi presensi pada jadwal aktif melalui `GuruController::openSession()`. Sesi menghasilkan token acak unik dengan masa kedaluwarsa tertentu (`expires_at`).
2. **Display QR**: Layar menampilkan QR Code (SVG/Base64) yang dibuat menggunakan `QrCodeService`.
3. **Scan oleh Siswa**: Siswa mengakses kamera via browser (`SiswaController::scanPage()`), memindai QR, dan browser mengambil koordinat GPS (latitude, longitude, accuracy).
4. **Validasi Geolocation**:
   - `GeoLocationService::validate()` menghitung jarak pengguna terhadap koordinat `system_settings` menggunakan rumus **Haversine**.
   - Jika jarak melampaui `allowed_radius_meters`, status presensi ditandai `outside_radius` atau ditolak bergantung setting `fallback_on_location_error`.
5. **Pencatatan Presensi**: `StudentAttendance` menyimpan status (`hadir`, `terlambat`, dll), koordinat, akurasi, dan jarak dalam meter. Dibatasi oleh konstrain unik `unique_student_schedule_date`.

### 4.2. Presensi Guru (Self Check-In & Monitoring)
- Guru melakukan check-in mandiri pada jadwal yang diampu (`GuruController::teacherSelfCheckIn()`).
- Kehadiran guru tercatat di `TeacherAttendance` dan dapat dipantau langsung oleh Admin Kurikulum (`AdminKurikulumController::teacherAttendanceMonitor()`).

### 4.3. Laporan Kondisi Kelas (Admin Kelas -> Kurikulum)
- Pengurus kelas (`admin_kelas`) melaporkan keterlaksanaan KBM setiap jam pelajaran melalui `AdminKelasController::storeReport()`.
- Data meliputi: status kehadiran guru (hadir / terlambat / tidak hadir), keberadaan tugas pengganti, catatan siswa tidak hadir, dan kondisi ketertiban.
- Admin Kurikulum meninjau dan melakukan **Approve** atau **Reject** atas laporan tersebut.

### 4.4. Penegakan Disiplin & Pemanggilan Orang Tua
- Admin Kedisiplinan mencatat pelanggaran siswa berdasarkan master `violation_types` beserta bobot poin.
- Jika akumulasi poin melampaui batas ambang atau memerlukan tindakan khusus, diterbitkan `ParentSummon` (Surat Panggilan Orang Tua) lengkap dengan nomor surat resmi, waktu, tempat pertemuan di Ruang BK, dan format cetak siap pakai.

### 4.5. Pengaturan Sistem Global (`system_settings`)
- Berisi koordinat sekolah (`school_latitude`, `school_longitude`), batas radius meter (`allowed_radius_meters`), akurasi GPS maksimal, nama kepala sekolah, dan NIP.
- Menggunakan pola singleton melalui method `SystemSetting::current()`.

---

## 5. Standar Kode & Konvensi Pengembangan

### 5.1. PHP & Laravel Style
- Gunakan fitur modern PHP 8.3+: `typed properties`, `match` expressions, constructor property promotion, return types eksplisit, dan `readonly` properties jika imutabel.
- Selalu deklarasikan method casts Eloquent menggunakan array syntax model baru:
  ```php
  protected function casts(): array
  {
      return [
          'is_active' => 'boolean',
          'meeting_date' => 'date',
      ];
  }
  ```
- **Pencegahan N+1 Query**: Selalu lakukan eager-loading relasi menggunakan `with([...])` saat membaca query daftar/tabel.
- Jangan letakkan query DB yang kompleks di dalam Blade views. Olah data di Controller atau Service.
- Validasi data request selalu menggunakan Form Request atau `$request->validate([...])` dengan aturan yang ketat.

### 5.2. Frontend & Blade Conventions
- Styling memanfaatkan utility classes Tailwind CSS v4.
- Komponen reusable diletakkan pada `resources/views/components/` atau `resources/views/partials/`.
- Sediakan flash feedback untuk setiap interaksi form (`session('success')` atau `session('error')`).
- Halaman cetak (surat panggilan orang tua, rekap absensi) harus mendukung CSS print (`@media print`) agar rapi saat dicetak ke PDF/kertas fisik.

### 5.3. Penanganan File & Storage
- File surat izin/sakit siswa diunggah melalui `AttendanceAttachmentController` dan disimpan pada storage disk `public` atau `local` yang terproteksi.
- Gunakan `Storage::disk('public')->putFile()` dan pastikan symlink storage (`php artisan storage:link`) sudah terpasang.

---

## 6. Perintah Operasional Penting (CLI Reference)

```bash
# Menjalankan server aplikasi lokal
php artisan serve

# Menjalankan Vite bundler untuk frontend Tailwind v4
npm run dev

# Melakukan kompilasi aset untuk produksi
npm run build

# Menjalankan migrasi database
php artisan migrate

# Menjalankan migrasi bersih beserta seeding
php artisan migrate:fresh --seed

# Menjalankan automated test suite
php artisan test

# Memeriksa dan memformat code style PSR-12 / Laravel
./vendor/bin/pint
```

---

## 7. Instruksi Khusus untuk AI Agents

Saat bertindak dalam repositori ini, agen **WAJIB** mematuhi ketentuan berikut:
1. **Integritas Skema Database**: Jangan pernah memodifikasi file migration yang sudah dijalankan jika proyek sudah berada di tahap aktif; buat file migration baru untuk perubahan skema.
2. **Kesesuaian Hak Akses (Security First)**: Setiap penambahan rute (route) baru **wajib** dilindungi oleh middleware auth dan role yang sesuai. Jangan membiarkan rute manajemen terbuka tanpa proteksi middleware.
3. **Audit Trail**: Tindakan administratif kritis (reset password, penghapusan user, perubahan jadwal resmi, modifikasi pengaturan koordinat sekolah) disarankan mencatat entri ke model `AuditLog`.
4. **Koordinat & Privasi**: Koordinat lokasi sekolah diambil dari `SystemSetting::current()`, jangan melakukan hardcode koordinat di dalam controller atau JavaScript.
5. **Bahasa Antarmuka**: Seluruh tampilan UI, label formulir, notifikasi toast/alert, dan nama status absensi menggunakan **Bahasa Indonesia** sesuai konteks lingkungan sekolah SMAN 5 Karawang.
