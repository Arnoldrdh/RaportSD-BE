<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
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
            'code' => 'required|integer|max:1',
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

        //kurang check kalau ada siswa gak bisa dihapus
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
}
