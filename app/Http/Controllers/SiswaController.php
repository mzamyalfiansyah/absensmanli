<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\SystemSetting;
use App\Models\Violation;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    
    private function getStudent(): ?Student
    {
        $user = Auth::user();

        if ($user->student) {
            return $user->student->load('schoolClass');
        }

        if ($user->role === 'superadmin') {
            return Student::with('schoolClass')->first();
        }

        return null;
    }


    public function scanPage(): View
    {
        $student = $this->getStudent();
        if (! $student) {
            abort(403, 'Akun Anda belum terdaftar sebagai data Siswa.');
        }

        $today = Carbon::today()->toDateString();
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $currentDay = $dayMap[Carbon::now()->format('l')] ?? 'senin';

        $todaySchedules = Schedule::with(['subject', 'teacher'])
            ->where('class_id', $student->class_id)
            ->where('day_of_week', $currentDay)
            ->get();

        $activeSessions = AttendanceSession::whereIn('schedule_id', $todaySchedules->pluck('id'))
            ->where('date', $today)
            ->where('status', 'open')
            ->where('expires_at', '>', Carbon::now())
            ->get()
            ->keyBy('schedule_id');

        $myAttendancesToday = StudentAttendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('schedule_id');

        $settings = SystemSetting::current();

        return view('siswa.scan', compact('student', 'todaySchedules', 'activeSessions', 'myAttendancesToday', 'settings'));
    }

    public function submitAttendance(Request $request): JsonResponse|RedirectResponse
    {
        $student = $this->getStudent();
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai siswa.',
            ], 403);
        }

        $validated = $request->validate([
            'qr_token' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
        ]);

        $settings = SystemSetting::current();
        $now = Carbon::now();
        $today = $now->toDateString();

        $session = AttendanceSession::with('schedule.schoolClass')->where('token', $validated['qr_token'])->first();
        if (! $session) {
            AuditLog::record('suspicious_qr_scan', [
                'reason' => 'Invalid QR token',
                'token' => $validated['qr_token'],
                'student_id' => $student->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau bukan dikeluarkan oleh sistem SMAN 5 Karawang.',
            ], 422);
        }

        if (! $session->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi absensi ini telah ditutup atau masa berlakunya telah berakhir.',
            ], 422);
        }

        $schedule = $session->schedule;

        if ($student->class_id !== $schedule->class_id) {
            AuditLog::record('unauthorized_class_scan', [
                'student_class_id' => $student->class_id,
                'schedule_class_id' => $schedule->class_id,
                'student_id' => $student->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar pada kelas '.($schedule->schoolClass->name ?? '').' untuk sesi ini.',
            ], 403);
        }

        $existing = StudentAttendance::where('schedule_id', $schedule->id)
            ->where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah tercatat melakukan absensi pada sesi pelajaran ini hari ini ('.ucfirst($existing->status).').',
            ], 422);
        }

        $userLat = isset($validated['latitude']) ? (float) $validated['latitude'] : null;
        $userLon = isset($validated['longitude']) ? (float) $validated['longitude'] : null;
        $userAcc = isset($validated['accuracy']) ? (float) $validated['accuracy'] : null;

        $geoCheck = GeoLocationService::validate($userLat, $userLon, $userAcc, $settings);

        if (! $geoCheck['valid']) {
            AuditLog::record('location_rejected', [
                'student_id' => $student->id,
                'lat' => $userLat,
                'lon' => $userLon,
                'accuracy' => $userAcc,
                'distance' => $geoCheck['distance_meters'],
                'message' => $geoCheck['message'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $geoCheck['message'],
                'geo' => $geoCheck,
            ], 422);
        }

        $status = 'hadir';
        $scheduleStartTime = Carbon::parse($today.' '.$schedule->start_time);
        if ($now->isAfter($scheduleStartTime->copy()->addMinutes(15))) {
            $status = 'terlambat';
        }

        $attendance = StudentAttendance::create([
            'attendance_session_id' => $session->id,
            'schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'date' => $today,
            'status' => $status,
            'scanned_at' => $now,
            'latitude' => $userLat,
            'longitude' => $userLon,
            'accuracy' => $userAcc,
            'distance_meters' => $geoCheck['distance_meters'],
            'location_status' => $geoCheck['status'],
            'notes' => $geoCheck['need_verification'] ? 'Perlu verifikasi: sinyal GPS lemah / izin lokasi terbatas' : null,
        ]);

        AuditLog::record('student_scan_success', [
            'student_id' => $student->id,
            'schedule_id' => $schedule->id,
            'status' => $status,
            'distance' => $geoCheck['distance_meters'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat! Status: '.ucfirst($status).". ({$geoCheck['message']})",
            'attendance' => $attendance,
            'geo' => $geoCheck,
        ]);
    }

    
    public function history(Request $request): View
    {
        $user = Auth::user();

        $isSuperAdmin = $user->role === 'superadmin';

        $student = null;
        $classes = collect();

        if ($isSuperAdmin) {
            $classes = \Illuminate\Support\Facades\DB::table('classes')
    ->orderBy('id')
    ->get();

            $query = StudentAttendance::with([
                'student.schoolClass',
                'schedule.subject',
                'schedule.teacher',
            ]);

            if ($request->filled('class_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }
        } else {
            $student = $this->getStudent();

            if (! $student) {
                abort(403, 'Akun Anda belum terdaftar sebagai data siswa.');
            }

            $query = StudentAttendance::with([
                'student.schoolClass',
                'schedule.subject',
                'schedule.teacher',
            ])->where('student_id', $student->id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('siswa.history', compact(
            'student',
            'attendances',
            'classes',
            'isSuperAdmin'
        ));
    }
        

    
    public function violations(Request $request): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'superadmin';

        $student = null;
        $classes = collect();

        if ($isSuperAdmin) {
            $classes = DB::table('classes')
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

            $query = Violation::with([
                'student.schoolClass',
                'violationType',
            ]);

            if ($request->filled('class_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->filled('student_name')) {
                $name = $request->student_name;

                $query->whereHas('student', function ($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%');
                });
            }
        } else {
            $student = $this->getStudent();

            if (! $student) {
                abort(403, 'Akses ditolak.');
            }

            $query = Violation::with('violationType')
                ->where('student_id', $student->id);
        }

        $totalPoints = (clone $query)->sum('points');

        $violations = $query
            ->orderByDesc('incident_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

       

        return view('siswa.violations', compact(
            'student',
            'violations',
            'totalPoints',
            'classes',
            'isSuperAdmin'
        ));
    }

}