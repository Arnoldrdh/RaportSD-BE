<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    protected $table = 'class_students';

    protected $fillable = ['user_id', 'classroom_id'];

    public $timestamps = true; // Karena di migration  punya timestamps

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
