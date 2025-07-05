<?php

namespace App\Http\Middleware;

use App\Models\Period;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNoPeriodActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $period = Period::where('status', 'aktif')->first();
        if ($period) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak bisa melakukan aksi saat ada periode aktif'
            ], 403);
        }

        return $next($request);
    }
}
