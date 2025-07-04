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
            ->with(['period', 'classroom'])
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
}
