<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportItem extends Model
{
    protected $fillable = [
        'report_id',
        'course_id',
        'score', // Tambahkan 'score' di sini
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}