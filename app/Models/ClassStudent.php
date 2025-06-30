<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    protected $table = 'class_students'; // Sesuai dengan nama tabel di database

    protected $fillable = ['user_id', 'classroom_id'];

    public $timestamps = true; // Karena di migration kamu punya timestamps

    // Relasi ke user (siswa)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke kelas
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
