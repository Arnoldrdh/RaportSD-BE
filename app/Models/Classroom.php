<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'grade',
        'year',
        'code',
        'class_teacher'
    ];

    public function homeTeacher()
    {
        return $this->belongsTo(User::class, 'class_teacher');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_students', 'classroom_id', 'user_id')
            ->withTimestamps();
    }

    public function classStudents()
    {
        return $this->hasMany(ClassStudent::class, 'classroom_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
