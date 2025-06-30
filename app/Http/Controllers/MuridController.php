<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MuridController extends Controller
{

    public function getReportHistory()
    {
        $murid = Auth::user();

        $history = $murid->reports()
            ->with(['period', 'classroom', 'items.course'])
            ->orderBy('created_at', 'desc') 
            ->get();

        if ($history->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada riwayat rapot yang ditemukan.',
                'data' => []
            ], 200);
        }

        // 4. Kembalikan data riwayat sebagai JSON.
        return response()->json(['data' => $history]);
    }
}