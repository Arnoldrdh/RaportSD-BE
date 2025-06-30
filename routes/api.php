<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControllerKepalaSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return response()->json(Auth::user());
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:api', 'role:kepala_sekolah'])->prefix('kepala-sekolah')->group(function () {

    Route::get('/teachers', [ControllerKepalaSekolah::class, 'listTeacher']);

    // Manajemen Kelas
    Route::get('/show-kelas', [ControllerKepalaSekolah::class, 'showKelas']); // lihat semua kelas
    Route::get('/kelas/{id}', [ControllerKepalaSekolah::class, 'getKelas']); // lihat kelas
    Route::post('/kelas', [ControllerKepalaSekolah::class, 'addKelas']); // tambah kelas
    Route::put('/kelas/{id}', [ControllerKepalaSekolah::class, 'updateKelas']); // edit kelas
    Route::delete('/kelas/{id}', [ControllerKepalaSekolah::class, 'deleteKelas']); // hapus kelas

    // Tambah route lain (mata pelajaran, periode, dll) bisa di sini
});


Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// //buat testing aja
// Route::get('/show-kelas', [ControllerKepalaSekolah::class, 'showKelas']);
// Route::post('/add-kelas', [ControllerKepalaSekolah::class, 'addKelas']);
// Route::put('/update-kelas/{id}', [ControllerKepalaSekolah::class, 'updateKelas']);
// Route::delete('/delete-kelas/{id}', [ControllerKepalaSekolah::class, 'deleteKelas']);
