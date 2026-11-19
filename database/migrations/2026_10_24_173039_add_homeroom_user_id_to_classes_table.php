<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('homeroom_user_id')
                ->nullable()
                ->after('homeroom_teacher_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Isi otomatis untuk kelas yang sudah ada: akun admin_walas
        // dengan nomor HP yang sama dengan wali kelas di tabel teachers.
        DB::statement(
            "UPDATE classes c
             JOIN teachers t ON t.id = c.homeroom_teacher_id
             JOIN users u ON u.phone_number = t.phone AND u.role = 'admin_walas'
             SET c.homeroom_user_id = u.id
             WHERE c.homeroom_user_id IS NULL"
        );
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('homeroom_user_id');
        });
    }
};