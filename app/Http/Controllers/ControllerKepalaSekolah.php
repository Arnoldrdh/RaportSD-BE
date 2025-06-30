<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassStudent;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class ControllerKepalaSekolah extends Controller
{
    //show list kelas
    public function showKelas()
    {
        $dataList = Classroom::all();

        $data = $dataList->map(function ($kelas) {
            return [
                'id' => $kelas->id,
                'grade' => $kelas->grade,
                'code' => $kelas->code,
                'year' => $kelas->year,
                'class_teacher' => $kelas->homeTeacher ? $kelas->homeTeacher->name : null,
                'students_count' => $kelas->students()->count(),
                'created_at' => $kelas->created_at,
                'updated_at' => $kelas->updated_at,
                ];
        });



        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    //add kelas
    public function addKelas(Request $request)
    {
        //validasi input
        $request->validate([
            'grade' => 'required|integer|min:1|max:6',
            'code' => 'required|string|size:1',
            'year' => 'required|integer',
            'class_teacher' => 'nullable|exists:users,id'
        ]);

        //check apakah kelas sudah ada
        $existingClass = Classroom::where('grade', $request->grade)
            ->where('code', $request->code)
            ->where('year', $request->year)
            ->exists();

            
            //jika kelas ada, return error
            if ($existingClass) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kelas sudah ada'
                ], 400);
            }
            
            // Cek kalau ada class_teacher, pastikan dia role-nya wali kelas
           if ($request->filled('class_teacher')) {
               $user = User::find($request['class_teacher']);
               if ($user->role !== 'wali_kelas') {
                   return response()->json([
                       'status' => 'error',
                       'message' => 'User bukan wali kelas'
                   ], 400);
               }
           }

    // Kalau ada class_teacher, cek apakah usernya benar role wali_kelas
    if (!empty($validated['class_teacher'])) {
        $user = User::find($request['class_teacher']);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if ($user->role !== 'wali_kelas') {
            return response()->json([
                'status' => 'error',
                'message' => 'User yang dipilih bukan wali kelas'
            ], 403);
        }
    }

        //create kelas baru
        $kelas = Classroom::create([
            'grade' => $request->grade,
            'year' => $request->year,
            'code' => $request->code,
            'class_teacher' => $request->class_teacher ? $request->class_teacher : null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $kelas
        ], 201);
    }

    //update kelas
    public function updateKelas(Request $request, $id)
    {
        $dataClass = Classroom::findOrFail($id);

        //validasi input
        $request->validate([
            'grade' => 'required|integer|min:1|max:6',
            'code' => 'required|integer|min:1|max:6',
            'year' => 'required|integer',
        ]);

        //update kelas
        $dataClass->update($request->only(['grade', 'code', 'year']));
        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil diperbarui',
            'data' => $dataClass
        ], 200);
    }

    //delete kelas
    public function deleteKelas($id)
    {
        $dataClass = Classroom::findOrFail($id);

        // cek relasi siswa via method students()
        if ($dataClass->students()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kelas tidak bisa dihapus karena masih ada siswa yang terdaftar'
            ], 400);
        }

        $dataClass->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil dihapus'
        ], 200);
    }


    //logic manajement murid

    //tambah murid ke kelas
    public function assignStudentToClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $class = Classroom::findOrFail($request->class_id);
        $user = User::findOrFail($request->user_id);

        // Cek apakah user adalah murid
        if ($user->role !== 'siswa') {
            return response()->json(['error' => 'User bukan siswa'], 403);
        }

        // Cek apakah sudah masuk kelas yang sama
        if ($class->students()->where('users.id', $user->id)->exists()) {
            return response()->json(['error' => 'Siswa sudah terdaftar di kelas ini'], 409);
        }

        // Masukkan siswa ke kelas
        $class->students()->attach($user);

        return response()->json(['message' => 'Siswa berhasil ditambahkan ke kelas'], 200);
    }

    //update murid di kelas (belum)
    public function updateMuridKelas(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_class_id' => 'required|exists:classrooms,id',
        ]);

        $userId = $request->user_id;
        $newClassId = $request->new_class_id;

        // Ambil entri ClassStudent lama
        $currentClassEntry = ClassStudent::where('user_id', $userId)->first();

        // Jika tidak ada data sebelumnya
        if (!$currentClassEntry) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa belum terdaftar di kelas manapun',
            ], 404);
        }

        // Cek apakah siswa sudah memiliki rapor di kelas saat ini
        $hasReport = Report::where('user_id', $userId)
            ->where('class_id', $currentClassEntry->classroom_id)
            ->exists();

        if ($hasReport) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa sudah memiliki rapor di kelas saat ini dan tidak dapat dipindahkan',
            ], 403);
        }

        // Update classroom_id ke kelas baru
        $currentClassEntry->classroom_id = $newClassId;
        $currentClassEntry->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil dipindahkan ke kelas baru',
        ], 200);
    }
    

}
