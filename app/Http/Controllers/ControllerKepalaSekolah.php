<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;

class ControllerKepalaSekolah extends Controller
{
    //show list kelas
    public function showKelas()
    {
        $data = Classroom::all();
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
            'code' => 'required|integer|max:1',
            'year' => 'required|integer',
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

        //create kelas baru
        $kelas = Classroom::create([
            'grade' => $request->grade,
            'year' => $request->year,
            'code' => $request->code,
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

        //check kalau ada siswa gak bisa dihapus
        if($dataClass->student()->exists()) {
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
        $class->students()->attach($user->id);

        return response()->json(['message' => 'Siswa berhasil ditambahkan ke kelas'], 200);
    }

    //update murid di kelas (belum)

}
