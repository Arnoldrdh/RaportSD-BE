<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
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

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
