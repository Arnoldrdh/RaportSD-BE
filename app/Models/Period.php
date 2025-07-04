<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = [
        'semester',
        'year',
        'status',
    ];

    /**
     * Get the status of the period.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->attributes['status'];
    }

    /**
     * Set the status of the period.
     *
     * @param string $value
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
    }


    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
