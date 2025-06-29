<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControllerKepalaSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
});

//buat testing aja
Route::get('/show-kelas', [ControllerKepalaSekolah::class, 'showKelas']);
Route::post('/add-kelas', [ControllerKepalaSekolah::class, 'addKelas']);
Route::put('/update-kelas/{id}', [ControllerKepalaSekolah::class, 'updateKelas']);
Route::delete('/delete-kelas/{id}', [ControllerKepalaSekolah::class, 'deleteKelas']);
