<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClassConditionReport;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Services\CsvService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\AcademicYear;
use App\Models\Subject;

class AdminKurikulumController extends Controller
{

        // ==========================================
    // 0. KELOLA JADWAL PELAJARAN (CRUD)
    // ==========================================

    public function schedulesIndex(Request $request): View
    {
        $query = Schedule::with(['academicYear', 'schoolClass', 'subject', 'teacher']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('day')) {
            $query->where('day_of_week', $request->day);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(20)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();
        $activeYear = AcademicYear::getActive();

        $scheduleRoutes = [
            'index'   => 'kurikulum.schedules.index',
            'store'   => 'kurikulum.schedules.store',
            'update'  => 'kurikulum.schedules.update',
            'destroy' => 'kurikulum.schedules.destroy',
        ];

        return view('admin.master.schedules', compact(
            'schedules', 'classes', 'subjects', 'teachers', 'activeYear', 'scheduleRoutes'
        ));
    }

    // ==========================================
    // 1. MONITOR KEHADIRAN GURU PER JADWAL
    // ==========================================

    public function teacherAttendanceMonitor(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $dayMap = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $targetCarbon = Carbon::parse($date);
        $dayOfWeek = $dayMap[$targetCarbon->format('l')] ?? 'senin';

        $query = Schedule::with(['schoolClass', 'subject', 'teacher'])
            ->where('day_of_week', $dayOfWeek);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $schedules = $query->orderBy('start_time')->get();

        $teacherAttendances = TeacherAttendance::where('date', $date)
            ->get()
            ->keyBy('schedule_id');

        $classReports = ClassConditionReport::where('date', $date)
            ->get()
            ->keyBy('schedule_id');

        $classes = SchoolClass::orderBy('name')->get();
        $teachers = Teacher::orderBy('name')->get();

        return view('kurikulum.monitor', compact('schedules', 'teacherAttendances', 'classReports', 'date', 'dayOfWeek', 'classes', 'teachers'));
    }

    // ==========================================
    // 2. VERIFIKASI LAPORAN ADMIN KELAS
    // ==========================================

    public function classReportsIndex(Request $request): View
    {
        $query = ClassConditionReport::with(['schoolClass', 'teacher', 'schedule.subject', 'submittedByUser', 'reviewedByUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        $reports = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();

        return view('kurikulum.reports.index', compact('reports', 'classes'));
    }

    public function classReportApprove(Request $request, ClassConditionReport $report): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $report->update([
                'status' => 'approved',
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => Carbon::now(),
                'review_notes' => $request->input('review_notes', 'Disetujui oleh Kurikulum.'),
            ]);

            // Reconcile and update / create TeacherAttendance based on verified AdminKelas report
            TeacherAttendance::updateOrCreate(
                [
                    'schedule_id' => $report->schedule_id,
                    'teacher_id' => $report->teacher_id,
                    'date' => $report->date,
                ],
                [
                    'status' => $report->teacher_status,
                    'check_in_time' => $report->teacher_arrival_time,
                    'verification_method' => 'admin_kelas_verified',
                    'has_task' => $report->has_task,
                    'task_description' => $report->task_description,
                    'verified_by_user_id' => auth()->id(),
                    'verified_at' => Carbon::now(),
                    'notes' => 'Diverifikasi dari laporan Admin Kelas: '.($report->class_condition_notes ?? '-'),
                ]
            );

            DB::commit();
            AuditLog::record('approve_class_report', "Menyetujui laporan kondisi kelas {$report->schoolClass->name} tanggal {$report->date->format('Y-m-d')}");

            return back()->with('success', 'Laporan kelas berhasil diverifikasi dan kehadiran guru telah direkonsiliasi.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memverifikasi laporan: '.$e->getMessage());
        }
    }

    public function classReportReject(Request $request, ClassConditionReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string'],
        ], [
            'review_notes.required' => 'Berikan alasan penolakan laporan kelas.',
        ]);

        $report->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => Carbon::now(),
            'review_notes' => $validated['review_notes'],
        ]);

        AuditLog::record('reject_class_report', "Menolak laporan kelas {$report->schoolClass->name} dengan alasan: {$validated['review_notes']}");

        return back()->with('success', 'Laporan kelas ditolak.');
    }

    // ==========================================
    // 3. REKAP & LAPORAN KEHADIRAN (PRINT & CSV)
    // ==========================================

    public function studentAttendanceRecap(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $classId = $request->input('class_id');

        $query = StudentAttendance::with(['student.schoolClass', 'schedule.subject'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();

        // Summary counts
        $summary = [
            'hadir' => (clone $query)->where('status', 'hadir')->count(),
            'terlambat' => (clone $query)->where('status', 'terlambat')->count(),
            'izin' => (clone $query)->where('status', 'izin')->count(),
            'sakit' => (clone $query)->where('status', 'sakit')->count(),
            'alpa' => (clone $query)->where('status', 'alpa')->count(),
        ];

        $classes = SchoolClass::orderBy('name')->get();

        return view('kurikulum.recaps.student', compact('attendances', 'classes', 'startDate', 'endDate', 'classId', 'summary'));
    }

    public function teacherAttendanceRecap(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $teacherId = $request->input('teacher_id');

        $query = TeacherAttendance::with(['teacher', 'schedule.schoolClass', 'schedule.subject', 'verifiedByUser'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();

        $summary = [
            'hadir' => (clone $query)->where('status', 'hadir')->count(),
            'terlambat' => (clone $query)->where('status', 'terlambat')->count(),
            'tidak_hadir' => (clone $query)->where('status', 'tidak_hadir')->count(),
            'tidak_hadir_dengan_tugas' => (clone $query)->where('status', 'tidak_hadir')->where('has_task', true)->count(),
            'tidak_hadir_tanpa_tugas' => (clone $query)->where('status', 'tidak_hadir')->where('has_task', false)->count(),
        ];

        $teachers = Teacher::orderBy('name')->get();

        return view('kurikulum.recaps.teacher', compact('attendances', 'teachers', 'startDate', 'endDate', 'teacherId', 'summary'));
    }

    public function printRecap(Request $request): View
    {
        $type = $request->input('type', 'teacher');
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        if ($type === 'student') {
            $query = StudentAttendance::with(['student.schoolClass', 'schedule.subject'])
                ->whereBetween('date', [$startDate, $endDate]);
            if ($request->filled('class_id')) {
                $query->whereHas('student', fn ($q) => $q->where('class_id', $request->class_id));
            }
            $data = $query->orderBy('date')->get();
        } else {
            $query = TeacherAttendance::with(['teacher', 'schedule.schoolClass', 'schedule.subject'])
                ->whereBetween('date', [$startDate, $endDate]);
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }
            $data = $query->orderBy('date')->get();
        }

        return view('kurikulum.recaps.print', compact('data', 'type', 'startDate', 'endDate'));
    }

    public function exportTeacherCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $query = TeacherAttendance::with(['teacher', 'schedule.schoolClass', 'schedule.subject', 'verifiedByUser'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $records = $query->orderBy('date')->get();

        $headers = ['Tanggal', 'NIP Guru', 'Nama Guru', 'Mata Pelajaran', 'Kelas', 'Status', 'Waktu Masuk', 'Ada Tugas?', 'Keterangan Tugas', 'Metode Verifikasi'];
        $rows = [];

        foreach ($records as $r) {
            $rows[] = [
                $r->date->format('Y-m-d'),
                $r->teacher->nip ?? '',
                $r->teacher->name ?? '',
                $r->schedule->subject->name ?? '',
                $r->schedule->schoolClass->name ?? '',
                $r->status,
                $r->check_in_time ?? '-',
                $r->has_task ? 'Ya' : 'Tidak',
                $r->task_description ?? '-',
                $r->verification_method,
            ];
        }

        AuditLog::record('export_teacher_attendance', 'Mengekspor rekap absensi guru ke CSV');

        return CsvService::downloadCsv('rekap_kehadiran_guru_smanli_'.$startDate.'_'.$endDate.'.csv', $headers, $rows);
    }

    // ==========================================
    // 4. REKAP ABSENSI BULANAN SISWA
    // ==========================================

    public function studentAttendanceMonthlyRecap(Request $request): View
    {
        $year  = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);
        $classId = $request->input('class_id');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        // Ambil semua siswa yang sesuai filter kelas
        $studentQuery = Student::with('schoolClass')->orderBy('name');
        if ($classId) {
            $studentQuery->where('class_id', $classId);
        }
        $students = $studentQuery->get();
        $studentIds = $students->pluck('id')->toArray();

        // Ambil semua absensi dalam rentang bulan ini untuk siswa yang terfilter
        $attendances = StudentAttendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('student_id', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('student_id', 'status')
            ->get();

        // Buat map: student_id => ['hadir' => n, 'terlambat' => n, ...]
        $attendanceMap = [];
        foreach ($attendances as $att) {
            $attendanceMap[$att->student_id][$att->status] = $att->total;
        }

        // Tambahkan total ke setiap siswa
        $recap = $students->map(function ($student) use ($attendanceMap) {
            $data = $attendanceMap[$student->id] ?? [];
            return [
                'student'   => $student,
                'hadir'     => $data['hadir'] ?? 0,
                'terlambat' => $data['terlambat'] ?? 0,
                'izin'      => $data['izin'] ?? 0,
                'sakit'     => $data['sakit'] ?? 0,
                'alpa'      => $data['alpa'] ?? 0,
                'total'     => array_sum($data),
            ];
        });

        // Summary total keseluruhan
        $summary = [
            'hadir'     => $recap->sum('hadir'),
            'terlambat' => $recap->sum('terlambat'),
            'izin'      => $recap->sum('izin'),
            'sakit'     => $recap->sum('sakit'),
            'alpa'      => $recap->sum('alpa'),
            'total'     => $recap->sum('total'),
        ];
        $percentage = $summary['total'] > 0
            ? round((($summary['hadir'] + $summary['terlambat']) / $summary['total']) * 100, 1)
            : 0;

        $classes  = SchoolClass::orderBy('name')->get();
        $monthLabel = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        // Daftar tahun untuk dropdown (5 tahun ke belakang sampai 1 tahun ke depan)
        $years = range(Carbon::now()->year - 4, Carbon::now()->year + 1);

        return view('kurikulum.recaps.monthly', compact(
            'recap', 'summary', 'percentage', 'classes',
            'year', 'month', 'monthLabel', 'classId', 'years', 'startDate', 'endDate'
        ));
    }

    // ==========================================
    // 5. DATA GRAFIK ABSENSI BULANAN (JSON)
    // ==========================================

    /**
     * Mengembalikan data JSON untuk grafik absensi bulanan selama 12 bulan terakhir
     * atau 1 tahun penuh sesuai filter.
     */
    public function attendanceChartData(Request $request): JsonResponse
    {
        $year    = (int) $request->input('year', Carbon::now()->year);
        $month   = $request->filled('month') ? (int) $request->input('month') : null;
        $classId = $request->input('class_id');

        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }

        $statuses = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'];
        $labels   = [];
        $datasets = array_fill_keys($statuses, []);

        $baseQuery = function (string $start, string $end) use ($classId) {
            $query = StudentAttendance::whereBetween('date', [$start, $end]);
            if ($classId) {
                $query->whereHas('student', fn ($q) => $q->where('class_id', $classId));
            }
            return $query;
        };

        if ($month) {
            // MODE HARIAN: 1 bulan tertentu, per tanggal
            $first = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $last  = $first->copy()->endOfMonth();

            $rows = $baseQuery($first->toDateString(), $last->toDateString())
                ->select('date', 'status', DB::raw('COUNT(*) as total'))
                ->groupBy('date', 'status')
                ->get();

            // susun [tanggal][status] => total
            $map = [];
            foreach ($rows as $r) {
                $d = Carbon::parse($r->date)->day;
                $map[$d][$r->status] = (int) $r->total;
            }

            for ($d = 1; $d <= $first->daysInMonth; $d++) {
                $labels[] = (string) $d;
                foreach ($statuses as $status) {
                    $datasets[$status][] = $map[$d][$status] ?? 0;
                }
            }
        } else {
            // MODE TAHUNAN: 12 bulan (logika lama)
            for ($m = 1; $m <= 12; $m++) {
                $start = Carbon::createFromDate($year, $m, 1)->startOfMonth()->toDateString();
                $end   = Carbon::createFromDate($year, $m, 1)->endOfMonth()->toDateString();

                $labels[] = Carbon::createFromDate($year, $m, 1)->translatedFormat('M');

                $counts = $baseQuery($start, $end)
                    ->select('status', DB::raw('COUNT(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                foreach ($statuses as $status) {
                    $datasets[$status][] = $counts[$status] ?? 0;
                }
            }
        }

        return response()->json([
            'months'   => $labels,   // nama key dipertahankan agar JS lama tetap jalan
            'datasets' => $datasets,
            'year'     => $year,
            'month'    => $month,
            'classes'  => SchoolClass::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // ==========================================
    // 6. HALAMAN GRAFIK ABSENSI BULANAN
    // ==========================================

    public function attendanceChart(Request $request): View
    {
        $year    = (int) $request->input('year', Carbon::now()->year);
        $month   = $request->filled('month') ? (int) $request->input('month') : null;
        $classId = $request->input('class_id');
        $classes = SchoolClass::orderBy('name')->get();
        $years   = range(Carbon::now()->year - 4, Carbon::now()->year + 1);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('kurikulum.recaps.chart', compact('year', 'month', 'classId', 'classes', 'years', 'monthNames'));
    }
}