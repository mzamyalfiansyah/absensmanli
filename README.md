# Sistem Informasi Absensi Guru dan Siswa SMAN 5 Karawang

Aplikasi web presensi digital berbasis **Laravel 12**, **MySQL**, **Tailwind CSS v4**, dan **Blade**, yang dirancang khusus untuk memfasilitasi kebutuhan absensi guru dan siswa di SMA Negeri 5 Karawang. Sistem dilengkapi fitur **Dynamic QR Code**, **Geofencing GPS Radius Validasi**, **Manajemen Rekapitulasi Kurikulum & Walas**, **Pencatatan Pelanggaran Disiplin & Otomatisasi Surat Panggilan Orang Tua Ber-Kop Resmi**, serta **Laporan Kondisi Kelas Real-time**.

---

## 🌟 Fitur Utama Berdasarkan Peran (7 Role)

| Peran | Tanggung Jawab & Akses Fitur |
|---|---|
| **Super Admin** | Kelola akun pengguna, reset password, audit log seluruh mutasi sistem, konfigurasi geofencing sekolah (koordinat lat/long, toleransi radius meter, jam operasional), data master (Tahun Akademik, Kelas, Mata Pelajaran, Jadwal Pelajaran, Data Guru, Data Siswa, Import/Export CSV). |
| **Admin Kurikulum** | Monitoring presensi guru harian, validasi & persetujuan laporan kondisi kelas / guru piket, rekapitulasi kehadiran guru & siswa mingguan/bulanan/semester, ekspor CSV rekapitulasi, cetak lembar rekap resmi. |
| **Admin Kedisiplinan** | Master tipe pelanggaran dengan pembobotan poin, pencatatan insiden pelanggaran siswa, penerbitan surat panggilan orang tua (SP 1, SP 2, SP 3) otomatis lengkap dengan kop surat resmi Pemda Jabar & SMAN 5 Karawang siap cetak/PDF, ekspor data pelanggaran ke CSV. |
| **Admin Walas (Wali Kelas)** | Rekapitulasi absensi siswa binaan kelas, filter rentang tanggal, cetak rekap absensi kelas ber-kop surat resmi, monitoring catatan pelanggaran siswa di kelas binaan. |
| **Admin Kelas (Ketua/Sekretaris Kelas)** | Pengisian absensi manual siswa yang berhalangan hadir (Sakit, Izin, Alpha) untuk sesi pelajaran berjalan, pelaporan kondisi kelas (guru hadir / tugas mandiri / guru izin piket). |
| **Guru (Pengajar)** | Check-in kehadiran guru per jam mengajar dengan validasi radius GPS, pembukaan sesi absensi dinamis (Dynamic Rotating QR Code per 10-30 detik untuk mencegah titip absen), absensi manual siswa di kelas, riwayat kehadiran mengajar. |
| **Siswa** | Scan QR code dinamis guru pengajar menggunakan kamera smartphone/laptop dengan verifikasi radius GPS geofencing sekolah, melihat riwayat kehadiran pribadi, melihat status poin pelanggaran secara transparan. |

---

## 🔐 Kredensial Akun Bawaan (Default Demo)

Semua akun terdaftar menggunakan sandi bawaan berikut (dapat langsung digunakan atau dipilih melalui tombol klik cepat di halaman Login):

| Peran | Username / NIP / NIS | Password Default | Keterangan |
|---|---|---|---|
| **Super Admin** | `superadmin` | `admin123` | Akses penuh seluruh master data, setting geofencing & audit log |
| **Admin Kurikulum** | `adminkurikulum` | `admin123` | Monitoring guru, rekap presensi, & validasi laporan kelas |
| **Admin Kedisiplinan** | `adminkedisiplinan` | `admin123` | Pencatatan pelanggaran, poin, & terbit surat panggilan ortu |
| **Wali Kelas** | `adminwalas` | `admin123` | Wali Kelas X-1, pantau & cetak rekap absensi kelas binaan |
| **Admin Kelas (Siswa)** | `adminkelas` | `admin123` | Ketua/Sekretaris Kelas X-1, input absensi manual teman sekelas |
| **Guru (Pengajar)** | `197508102005011002` | `guru12345` | Drs. H. Mulyadi, M.Pd. (Matematika) |
| **Siswa** | `20261002` | `smanli2026` | Annisa Rahmawati (Kelas X-1) |

*Catatan: Sistem menerapkan middleware `EnsurePasswordChanged`. Beberapa akun siswa baru mungkin diminta mengganti sandi saat pertama kali login demi keamanan.*

---

## 🛠️ Persyaratan Sistem & Panduan Instalasi (Step-by-Step)

Bagi pengembang atau pengguna yang baru saja meng-clone projek ini dari GitHub, ikuti tahap-tahap berikut dari awal:

### 1. Prasyarat Sistem
Pastikan perangkat Anda telah terpasang:
- **PHP** >= 8.2 (dengan ekstensi: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `gd`, `fileinfo`)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM**
- **MySQL** >= 8.0 atau **MariaDB** >= 10.4 (bisa melalui XAMPP, MAMP, Laragon, atau MySQL native)
- **Git**

---

### 2. Langkah-Langkah Instalasi Lengkap

#### Langkah 1: Clone Repository
Buka terminal dan clone repository ini ke komputer Anda:
```bash
git clone <URL_REPOSITORY_GITHUB_ANDA>
cd Projek-absensi-smanli
```

#### Langkah 2: Install Dependensi PHP (Composer)
```bash
composer install
```

#### Langkah 3: Install Dependensi Frontend (Node.js)
```bash
npm install
```

#### Langkah 4: Siapkan File Environment (`.env`)
Salin file template `.env.example` menjadi `.env`:
- **macOS / Linux:**
  ```bash
  cp .env.example .env
  ```
- **Windows (CMD / PowerShell):**
  ```bash
  copy .env.example .env
  ```

#### Langkah 5: Generate Application Encryption Key
Jalankan perintah ini untuk membuat key enkripsi aplikasi (wajib agar tidak error 500):
```bash
php artisan key:generate
```

#### Langkah 6: Konfigurasi Database di File `.env`
Buka file `.env` di teks editor (VS Code, Sublime, dll), lalu sesuaikan bagian database sesuai aplikasi server lokal yang Anda gunakan:

- **Jika Menggunakan XAMPP atau Laragon (Port Default 3306):**
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=absensi_smanli
  DB_USERNAME=root
  DB_PASSWORD=
  ```

- **Jika Menggunakan MAMP di macOS (Port Default 8889):**
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=8889
  DB_DATABASE=absensi_smanli
  DB_USERNAME=root
  DB_PASSWORD=root
  ```

#### Langkah 7: Buat Database di MySQL
Buka **phpMyAdmin** (atau client database lain seperti TablePlus / DBeaver), kemudian buat database baru bernama:
```text
absensi_smanli
```

#### Langkah 8: Jalankan Migrasi & Seeder Database
Perintah ini akan membuat seluruh struktur tabel sekaligus menyuntikkan data master sekolah, jadwal, kelas, dan akun demo:
```bash
php artisan migrate --seed
```

#### Langkah 9: Hubungkan Direktori Penyimpanan (Storage Link)
Diperlukan agar avatar foto profil dan file lampiran dapat diakses publik:
```bash
php artisan storage:link
```

#### Langkah 10: Kompilasi Aset Frontend (Tailwind CSS & JS)
Kompilasi asset frontend menggunakan Vite:
```bash
npm run build
```
*(Tips: Jika Anda sedang dalam tahap pengembangan/mengubah tampilan, jalankan `npm run dev` pada terminal terpisah).*

#### Langkah 11: Jalankan Server Lokal
```bash
php artisan serve
```

Aplikasi siap diakses melalui browser pada alamat:
👉 **`http://localhost:8000`** atau **`http://127.0.0.1:8000`**

---

## 🧪 Pengujian Otomatis (Automated Testing)

Seluruh workflow, otentikasi, validasi geofencing, dynamic QR, dan otorisasi peran telah diuji secara komprehensif menggunakan PHPUnit:

```bash
php artisan test
```

**Hasil Pengujian:**
- 19 Tests Passed
- 74 Assertions
- 0 Failures

---

## 🛡️ Keamanan & Integritas Data

1. **Anti-Titip Absen:**
   - Dynamic QR Code berputar dengan token bertenggang waktu (Time-based expiring token 10-30 detik).
   - Validasi Geofencing GPS (Formula Haversine) memastikan siswa berada di dalam radius sekolah yang diizinkan (default 100m).
2. **Audit Logging:**
   - Setiap mutasi krusial (reset sandi, hapus data, status aktifasi akun, perubahan master) dicatat dalam tabel `audit_logs` bersama alamat IP dan rincian perubahan.
3. **Penyimpanan Berstandar:**
   - Password di-hash menggunakan algoritma Bcrypt.
   - Proteksi CSRF di seluruh form mutasi.
   - Validasi ketat pada setiap controller request.
