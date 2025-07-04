<?php

namespace App\Http\Controllers;

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
        $waliKelas = Auth::user();

        // Ambil kelas yang diajar oleh wali kelas ini, lalu ambil daftar muridnya
        $students = $waliKelas->taughtClass()
                              ->with('students:id,name,email') // Eager load data murid
                              ->first()
                              ->students;

        return response()->json(['data' => $students]);
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
}