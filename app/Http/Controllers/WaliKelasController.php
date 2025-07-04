<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Period;
use App\Models\Report;
use App\Models\ReportItem;

class WaliKelasController extends Controller
{
    /**
     * Mengambil daftar murid di kelas yang diampu oleh wali kelas yang sedang login.
     */
    public function getMyStudents()
    {
        $classrooms = $this->getTaughtClassroom();

        $report = Report::whereIn('classroom_id', $classrooms->pluck('id'))->with(['period', 'student', 'classroom'])->get();

        return response()->json(['data' => $report]);
    }

    /**
     * Melihat nilai mata pelajaran murid di periode aktif.
     */
    public function getStudentReport(Request $request)
    {
        $request->validate(['student_id' => 'required|exists:users,id']);

        $waliKelas = Auth::user();
        $studentId = $request->student_id;

        // Proteksi: Pastikan student_id adalah murid dari wali kelas ini
        if (!$waliKelas->taughtClass->students()->where('user_id', $studentId)->exists()) {
            return response()->json(['message' => 'Akses ditolak. Murid ini bukan bagian dari kelas Anda.'], 403);
        }

        $activePeriod = Period::where('is_active', true)->firstOrFail();

        // Cari rapot, atau buat baru jika belum ada untuk periode ini
        $report = Report::firstOrCreate(
            [
                'period_id' => $activePeriod->id,
                'user_id' => $studentId,
            ],
            [
                'classroom_id' => $waliKelas->taughtClass->id,
            ]
        );

        // Ambil data lengkap dengan relasinya
        $reportData = Report::with('items.course')->find($report->id);

        return response()->json(['data' => $reportData]);
    }

    public function getStudentReportById($id)
    {
        $classroom = $this->getTaughtClassroom();

        $report = Report::with(['reportItems.course', 'period', 'student', 'classroom'])->find($id);

        if ($report->classroom_id !== $classroom->id) {
            return response()->json(['message' => 'Akses ditolak. Rapot ini bukan milik kelas Anda.'], 403);
        }

        return response()->json(['data' => $report]);
    }

    public function listCourses()
    {
        $courses = Course::all();

        return response()->json(['data' => $courses]);
    }

    /**
     * Menambah atau mengedit nilai murid.
     */
    public function saveScore(Request $request)
    {
        $validated = $request->validate([
            'report_id' => 'required|exists:reports,id',
            'course_id' => 'required|exists:courses,id',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        // Proteksi bisa ditambahkan di sini untuk memastikan report_id milik muridnya

        $item = ReportItem::updateOrCreate(
            [
                'report_id' => $validated['report_id'],
                'course_id' => $validated['course_id'],
            ],
            [
                'score' => $validated['score']
            ]
        );

        return response()->json(['message' => 'Nilai berhasil disimpan', 'data' => $item]);
    }

    private function getTaughtClassroom()
    {
        $waliKelas = Auth::user();

        $classroom = Classroom::where('class_teacher', $waliKelas->id)->first();

        return $classroom;
    }
}
