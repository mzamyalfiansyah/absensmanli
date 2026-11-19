<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Academic Years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20); // e.g. "2025/2026"
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // 2. Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nip', 30)->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('phone', 20)->nullable();
            $table->string('specialization')->nullable(); // Bidang Studi
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 3. Classes
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // e.g. "X-1", "XI MIPA 2", "XII IPS 1"
            $table->string('level', 10); // "10", "11", "12"
            $table->string('major', 30)->default('Umum'); // "MIPA", "IPS", "Umum"
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('class_admin_user_id')->nullable()->constrained('users')->nullOnDelete(); // AdminKelas
            $table->timestamps();
        });

        // 4. Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->string('phone_parent', 25)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 5. Subjects (Mata Pelajaran)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 6. Schedules (Jadwal Pelajaran)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->enum('day_of_week', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->unsignedTinyInteger('period_start'); // Jam ke- (1)
            $table->unsignedTinyInteger('period_end');   // Jam ke- (2)
            $table->time('start_time'); // "07:15:00"
            $table->time('end_time');   // "08:45:00"
            $table->timestamps();

            $table->index(['class_id', 'day_of_week']);
            $table->index(['teacher_id', 'day_of_week']);
        });

        // 7. Attendance Sessions (Sesi Absensi dengan Dynamic QR)
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('date');
            $table->string('token', 80)->unique(); // Dynamic QR Token
            $table->string('title')->nullable();
            $table->enum('status', ['open', 'closed', 'expired'])->default('open');
            $table->dateTime('opened_at');
            $table->dateTime('expires_at');
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'date', 'status']);
        });

        // 8. Student Attendances (Absensi Siswa)
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->nullable()->constrained('attendance_sessions')->nullOnDelete();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'])->default('alpa');
            $table->dateTime('scanned_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('distance_meters', 8, 2)->nullable();
            $table->enum('location_status', ['valid', 'outside_radius', 'poor_accuracy', 'bypassed', 'manual'])->default('valid');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();

            // Mencegah absensi ganda siswa pada jadwal dan tanggal yang sama
            $table->unique(['schedule_id', 'student_id', 'date'], 'unique_student_schedule_date');
            $table->index(['student_id', 'date', 'status']);
        });

        // 9. Teacher Attendances (Absensi Guru per Jadwal)
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['hadir', 'terlambat', 'tidak_hadir'])->default('hadir');
            $table->time('check_in_time')->nullable();
            $table->enum('verification_method', ['self_check_in', 'qr_session', 'admin_kelas_verified', 'manual_kurikulum'])->default('qr_session');
            $table->boolean('has_task')->default(false); // Guru memberikan tugas jika tidak hadir
            $table->text('task_description')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'teacher_id', 'date'], 'unique_teacher_schedule_date');
            $table->index(['teacher_id', 'date', 'status']);
        });

        // 10. Class Condition Reports (Laporan Kondisi Kelas dari AdminKelas, diverifikasi AdminKurikulum)
        Schema::create('class_condition_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('submitted_by_user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('period_label', 50)->nullable(); // e.g. "Jam 1 - 2"
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->enum('teacher_status', ['hadir', 'terlambat', 'tidak_hadir'])->default('hadir');
            $table->time('teacher_arrival_time')->nullable();
            $table->boolean('has_task')->default(false);
            $table->text('task_description')->nullable();
            $table->text('absent_students_notes')->nullable();
            $table->text('class_condition_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'date', 'status']);
        });

        // 11. Violation Types (Jenis Pelanggaran Siswa)
        Schema::create('violation_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->enum('category', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->unsignedInteger('points')->default(5);
            $table->text('default_action')->nullable();
            $table->timestamps();
        });

        // 12. Violations (Catatan Pelanggaran Siswa oleh AdminKedisiplinan)
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('violation_type_id')->constrained('violation_types')->onDelete('cascade');
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location')->nullable();
            $table->text('chronology');
            $table->text('follow_up_action')->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('points')->default(5);
            $table->enum('status', ['tercatat', 'dalam_pembinaan', 'selesai', 'panggilan_ortu'])->default('tercatat');
            $table->timestamps();

            $table->index(['student_id', 'incident_date', 'status']);
        });

        // 13. Parent Summons (Surat Panggilan Orang Tua Siap Cetak / PDF)
        Schema::create('parent_summons', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number', 50)->unique(); // e.g. "421.3/012/SMAN5-KRW/2026"
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('violation_id')->nullable()->constrained('violations')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('meeting_place')->default('Ruang BP/BK SMAN 5 Karawang');
            $table->string('recipient_parent_name')->nullable();
            $table->string('acknowledged_by_title')->default('Wakasek Kesiswaan');
            $table->string('acknowledged_by_name')->default('Drs. Ahmad Junaedi, M.Pd.');
            $table->string('acknowledged_by_nip')->nullable();
            $table->string('homeroom_teacher_name')->nullable();
            $table->enum('status', ['draft', 'dikirim', 'hadir', 'tidak_hadir'])->default('dikirim');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'meeting_date']);
        });

        // 14. Audit Logs (Pencatatan Aktivitas Penting Sistem)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 15. System Settings (Pengaturan Koordinat Sekolah, Radius, Jam, dll)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('SMAN 5 Karawang');
            $table->string('school_npsn', 20)->default('20217777');
            $table->text('school_address')->nullable();
            $table->string('school_phone', 30)->nullable();
            $table->string('school_email', 100)->nullable();
            $table->string('headmaster_name')->default('H. Suhaerudin, M.Pd.');
            $table->string('headmaster_nip')->nullable();
            $table->decimal('school_latitude', 10, 7)->default(-6.3053800);
            $table->decimal('school_longitude', 10, 7)->default(107.3012500);
            $table->unsignedInteger('allowed_radius_meters')->default(100);
            $table->unsignedInteger('max_gps_accuracy_meters')->default(50);
            $table->boolean('require_location')->default(true);
            $table->enum('fallback_on_location_error', ['reject', 'need_verification'])->default('need_verification');
            $table->unsignedInteger('default_session_minutes')->default(45);
            $table->string('default_student_password')->default('smanli2026');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('parent_summons');
        Schema::dropIfExists('violations');
        Schema::dropIfExists('violation_types');
        Schema::dropIfExists('class_condition_reports');
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('academic_years');
    }
};
