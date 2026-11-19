<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttendanceAttachmentController extends Controller
{
    public function show(StudentAttendance $attendance): BinaryFileResponse
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403, 'Akun tidak terautentikasi dengan benar.');
        }

        abort_unless(
            $attendance->attachment && Storage::disk('local')->exists($attendance->attachment),
            404,
            'Lampiran tidak ditemukan.'
        );

        $attendance->loadMissing(['student', 'schedule']);
        $classId = $attendance->student->class_id;
        $teacher = $user->teacher;

        $allowed = $user->isSuperAdmin()
            || $user->role === 'admin_kurikulum'
            || ($user->role === 'admin_kelas'
                && SchoolClass::where('id', $classId)->where('class_admin_user_id', $user->id)->exists())
            || ($user->role === 'admin_walas' && $teacher
                && SchoolClass::where('id', $classId)->where('homeroom_teacher_id', $teacher->id)->exists())
            || ($user->role === 'guru' && $teacher
                && (int) $attendance->schedule->teacher_id === (int) $teacher->id)
            || ($user->role === 'siswa' && $user->student
                && (int) $user->student->id === (int) $attendance->student_id);

        abort_unless($allowed, 403, 'Anda tidak berhak melihat lampiran ini.');

        // Disk 'local' = storage/app/private (sesuai config/filesystems.php)
        return response()->file(storage_path('app/private/' . $attendance->attachment));
    }
}