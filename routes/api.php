<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControllerKepalaSekolah;
use App\Http\Controllers\WaliKelasController;
use App\Http\Controllers\MuridController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return response()->json(Auth::user());
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/all', [AuthController::class, 'getAllData']);

Route::middleware(['auth:api', 'role:kepala_sekolah'])->prefix('kepala-sekolah')->group(function () {

    Route::get('/teachers', [ControllerKepalaSekolah::class, 'listTeacher']);
    Route::get('/unvalidated-users', [ControllerKepalaSekolah::class, 'listUnvalidatedUser']);
    Route::patch('/users/{id}', [ControllerKepalaSekolah::class, 'updateUser']);

    // 🏫 Manajemen Kelas
    Route::get('/show-kelas', [ControllerKepalaSekolah::class, 'showKelas']);
    Route::get('/kelas/{id}', [ControllerKepalaSekolah::class, 'getKelas']);
    Route::post('/kelas', [ControllerKepalaSekolah::class, 'addKelas']);
    Route::put('/kelas/{id}', [ControllerKepalaSekolah::class, 'updateKelas']);
    Route::delete('/kelas/{id}', [ControllerKepalaSekolah::class, 'deleteKelas']);

    // 👩‍🎓 Manajemen Murid di Kelas
    Route::post('/kelas/assign-student', [ControllerKepalaSekolah::class, 'assignStudentToClass']); // tambah murid ke kelas
    Route::post('/kelas/move-student', [ControllerKepalaSekolah::class, 'moveStudent']); // pindah murid ke kelas lain

    // 📚 Manajemen Mata Pelajaran
    Route::get('/courses', [ControllerKepalaSekolah::class, 'listCourse']); // lihat
    Route::post('/courses', [ControllerKepalaSekolah::class, 'addCourse']); // tambah
    Route::put('/courses/{id}', [ControllerKepalaSekolah::class, 'updateCourse']); // edit
    Route::delete('/courses/{id}', [ControllerKepalaSekolah::class, 'deleteCourse']); // hapus (dengan proteksi periode aktif)

    // 📆 Manajemen Periode
    Route::post('/periods', [ControllerKepalaSekolah::class, 'addPeriod']); // tambah
    Route::put('/periods/{id}', [ControllerKepalaSekolah::class, 'updatePeriod']); // edit status
    Route::delete('/periods/{id}', [ControllerKepalaSekolah::class, 'deletePeriod']); // hapus periode pending saja
});

//Wali kelas Geys
Route::middleware('role:wali_kelas')->prefix('walikelas')->group(function () {
    Route::get('/courses', [WaliKelasController::class, 'listCourses']);

    Route::get('/my-students', [WaliKelasController::class, 'getMyStudents']);
    Route::get('/report', [WaliKelasController::class, 'getStudentReport']);
    Route::get('/report/{id}', [WaliKelasController::class, 'getStudentReportById']);
    Route::post('/report/{id}', [WaliKelasController::class, 'upsertStudentReport']);

    Route::post('/report/score', [WaliKelasController::class, 'saveScore']);
});

//Nek iki murid
Route::middleware('role:murid')->prefix('murid')->group(function () {
    Route::get('/history', [MuridController::class, 'getReportHistory']);
});
