<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MuridController extends Controller
{

    public function getReportHistory()
    {
        $murid = Auth::user();

        $history = Report::where('user_id', $murid->id)
            ->whereHas('period', function ($query) {
                $query->where('status', '!=', 'aktif');
            })
            ->with([
                'period',
                'classroom' => function ($query) {
                    $query->withTrashed();
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($history->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada riwayat rapot yang ditemukan.',
                'data' => []
            ], 200);
        }

        return response()->json(['data' => $history]);
    }

    public function getReportById($id)
    {
        $murid = Auth::user();


        $report = Report::where('id', $id)->with([
            'reportItems.course' => function ($query) {
                $query->withTrashed();
            },
            'classroom' => function ($query) {
                $query->withTrashed();
            },
            'period',
            'student'
        ])->first();

        if ($report->student->id !== $murid->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke rapot ini.',
                'data' => []
            ], 403);
        }

        return response()->json(['data' => $report]);
    }
}
