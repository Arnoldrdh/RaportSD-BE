<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ClassStudent;
use App\Models\Course;
use App\Models\Period;
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

    public function getKelas($id)
    {
        $data = Classroom::with(['homeTeacher'])->findOrFail($id);
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
            'code' => 'required|string|size:1',
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

        if (Period::where('status', 'aktif')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak bisa menghapus kelas saat ada periode aktif'
            ], 403);
        }

        $dataClass->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kelas berhasil dihapus'
        ], 200);
    }


    //manajement murid

    //tambah murid ke kelas
    public function assignStudentToClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classrooms,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $class = Classroom::findOrFail($request->class_id);
        $user = User::findOrFail($request->user_id);

        // Cek apakah user adalah murid
        if ($user->role !== 'murid') {
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

    //update murid di kelas 
    public function moveStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'new_class_id' => 'required|exists:classrooms,id',
        ]);

        $studentId = $request->student_id;
        $newClassId = $request->new_class_id;

        // Ambil data user
        $student = User::findOrFail($studentId);
        if ($student->role !== 'siswa') {
            return response()->json(['message' => 'User bukan siswa'], 403);
        }

        // Ambil record class_students terakhir (asumsi satu murid hanya 1 kelas)
        $currentClassStudent = ClassStudent::where('user_id', $studentId)->first();

        if (!$currentClassStudent) {
            return response()->json(['message' => 'Siswa belum tergabung di kelas manapun'], 404);
        }

        // Cek apakah periode aktif ada
        $activePeriod = Period::where('status', 'aktif')->first();
        if ($activePeriod) {
            $reportExists = Report::where('user_id', $studentId)
                ->where('class_id', $currentClassStudent->classroom_id)
                ->where('period_id', $activePeriod->id)
                ->exists();

            if ($reportExists) {
                return response()->json(['message' => 'Siswa tidak bisa dipindah karena sudah punya rapot di periode aktif'], 403);
            }
        }

        // Update ke kelas baru
        $currentClassStudent->update([
            'classroom_id' => $newClassId
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil dipindahkan ke kelas baru'
        ], 200);
    }

    //mamanagement Mata Pelajaran
    public function listCourse()
    {
        return response()->json([
            'status' => 'success',
            'data' => Course::all()
        ]);
    }

    public function addCourse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:courses,name'
        ]);

        $course = Course::create(['name' => $request->name]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil ditambahkan',
            'data' => $course
        ], 201);
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:courses,name,' . $id
        ]);

        $course->update(['name' => $request->name]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil diperbarui',
            'data' => $course
        ]);
    }

    public function deleteCourse($id)
    {
        // Proteksi: tidak boleh hapus jika ada periode aktif
        if (Period::where('status', 'aktif')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak bisa menghapus mata pelajaran saat ada periode aktif'
            ], 403);
        }

        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil dihapus'
        ]);
    }


    //management periode

    public function addPeriod(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'semester' => 'required',
        ]);

        // Cek duplikat
        if (Period::where('year', $request->year)->where('semester', $request->semester)->exists()) {
            return response()->json(['message' => 'Periode sudah ada.'], 400);
        }

        $period = Period::create([
            'year' => $request->year,
            'semester' => $request->semester,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Periode berhasil ditambahkan.', 'data' => $period], 201);
    }

    public function updatePeriod(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,pending, selesai'
        ]);

        $period = Period::findOrFail($id);

        // Jika ingin aktifkan, pastikan tidak ada periode lain yang aktif
        if ($request->status == 'aktif') {
            if (Period::where('status', 'aktif')->where('id', '!=', $id)->exists()) {
                return response()->json(['message' => 'Hanya boleh satu periode aktif'], 400);
            }
        }

        // Tidak bisa edit kalau sudah selesai
        if ($period->status === 'selesai') {
            return response()->json(['message' => 'Periode selesai tidak bisa diedit'], 403);
        }

        $period->status = $request->status;
        $period->save();

        return response()->json(['message' => 'Periode diperbarui']);
    }

    public function deletePeriod($id)
    {
        $period = Period::findOrFail($id);

        if ($period->status !== 'pending') {
            return response()->json(['message' => 'Hanya periode pending yang bisa dihapus'], 403);
        }

        $period->delete();

        return response()->json(['message' => 'Periode berhasil dihapus']);
    }


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


    public function listTeacher()
    {
        $data = User::where('role', 'wali_kelas')->get();

        return response()->json([
            'message' => 'success',
            'data' => $data
        ], 200);
    }

    public function listUnvalidatedUser()
    {
        $data = User::where("role", null)->get();

        return response()->json([
            'message' => 'success',
            'data' => $data
        ], 200);
    }

    public function updateUser(Request $request, $id)
    {
        $data = User::findOrFail($id);
        $data->update($request->only(['role']));
        return response()->json([
            'message' => 'success',
            'data' => $data
        ], 200);
    }
}
