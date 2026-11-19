<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvService
{
    /**
     * Read CSV rows into associative array.
     */
    public static function parseCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        // Check for UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Detect delimiter (comma vs semicolon)
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") {
            fseek($handle, 3);
        }

        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $header = null;
        $rows = [];

        while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
            if (! $header) {
                $header = array_map(function ($item) {
                    return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $item)));
                }, $data);

                continue;
            }

            if (empty(array_filter($data))) {
                continue;
            }

            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = isset($data[$i]) ? trim($data[$i]) : null;
            }
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Import Teachers from CSV.
     *
     * Expected columns: nip, nama, jenis_kelamin, bidang_studi, no_hp, alamat, email
     *
     * @return array{success: int, skipped: int, errors: array<string>}
     */
    public static function importTeachers(UploadedFile $file, ?string $defaultPassword = null): array
    {
        $rows = static::parseCsv($file);
        $settings = SystemSetting::current();
        $password = $defaultPassword ?: 'guru12345';

        $success = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $nip = $row['nip'] ?? $row['username'] ?? null;
                $name = $row['nama'] ?? $row['name'] ?? null;

                if (! $nip || ! $name) {
                    $errors[] = "Baris {$line}: NIP dan Nama wajib diisi.";
                    $skipped++;

                    continue;
                }

                // Check existing
                if (Teacher::where('nip', $nip)->exists() || User::where('username', $nip)->exists()) {
                    $errors[] = "Baris {$line}: Guru dengan NIP {$nip} sudah terdaftar, dilewati.";
                    $skipped++;

                    continue;
                }

                $gender = strtoupper(substr($row['jenis_kelamin'] ?? 'L', 0, 1));
                if (! in_array($gender, ['L', 'P'])) {
                    $gender = 'L';
                }

                $email = ! empty($row['email']) ? $row['email'] : null;
                if ($email && User::where('email', $email)->exists()) {
                    $email = null;
                }

                $user = User::create([
                    'name' => $name,
                    'username' => $nip,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'guru',
                    'is_active' => true,
                    'must_change_password' => true,
                    'phone_number' => $row['no_hp'] ?? null,
                ]);

                Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $nip,
                    'name' => $name,
                    'gender' => $gender,
                    'phone' => $row['no_hp'] ?? null,
                    'specialization' => $row['bidang_studi'] ?? $row['mapel'] ?? null,
                    'address' => $row['alamat'] ?? null,
                ]);

                $success++;
            }

            DB::commit();
            AuditLog::record('import_teachers', "Berhasil mengimpor {$success} guru, {$skipped} dilewati.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = 'Gagal memproses file: '.$e->getMessage();
        }

        return [
            'success' => $success,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Import Students from CSV.
     *
     * Expected columns: nis, nama, kelas, jenis_kelamin, nisn, no_hp_ortu, alamat
     *
     * @return array{success: int, skipped: int, errors: array<string>}
     */
    public static function importStudents(UploadedFile $file): array
    {
        $rows = static::parseCsv($file);
        $settings = SystemSetting::current();
        $defaultPassword = $settings->default_student_password ?: 'smanli2026';

        $success = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $nis = $row['nis'] ?? null;
                $name = $row['nama'] ?? $row['name'] ?? null;
                $className = $row['kelas'] ?? null;

                if (! $nis || ! $name || ! $className) {
                    $errors[] = "Baris {$line}: NIS, Nama, dan Kelas wajib diisi.";
                    $skipped++;

                    continue;
                }

                if (Student::where('nis', $nis)->exists() || User::where('username', $nis)->exists()) {
                    $errors[] = "Baris {$line}: Siswa dengan NIS {$nis} sudah ada, dilewati.";
                    $skipped++;

                    continue;
                }

                // Match class or find by name
                $class = SchoolClass::where('name', $className)->first();
                if (! $class) {
                    // Create class if not exists
                    $level = '10';
                    if (str_starts_with($className, 'XI') || str_starts_with($className, '11')) {
                        $level = '11';
                    } elseif (str_starts_with($className, 'XII') || str_starts_with($className, '12')) {
                        $level = '12';
                    }
                    $class = SchoolClass::create([
                        'name' => $className,
                        'level' => $level,
                        'major' => 'Umum',
                    ]);
                }

                $gender = strtoupper(substr($row['jenis_kelamin'] ?? 'L', 0, 1));
                if (! in_array($gender, ['L', 'P'])) {
                    $gender = 'L';
                }

                $user = User::create([
                    'name' => $name,
                    'username' => $nis,
                    'email' => null,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'siswa',
                    'is_active' => true,
                    'must_change_password' => true,
                    'phone_number' => $row['no_hp_ortu'] ?? null,
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'nis' => $nis,
                    'nisn' => $row['nisn'] ?? null,
                    'name' => $name,
                    'gender' => $gender,
                    'class_id' => $class->id,
                    'phone_parent' => $row['no_hp_ortu'] ?? null,
                    'address' => $row['alamat'] ?? null,
                ]);

                $success++;
            }

            DB::commit();
            AuditLog::record('import_students', "Berhasil mengimpor {$success} siswa, {$skipped} dilewati.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = 'Gagal memproses file: '.$e->getMessage();
        }

        return [
            'success' => $success,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Stream array of records as CSV download with UTF-8 BOM.
     */
    public static function downloadCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
