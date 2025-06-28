<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'grade',
        'year',
        'code',
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'class_students', 'classroom_id', 'user_id')
            ->withTimestamps();
    }
}
