<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $fillable = [

        'staff_id',
        'date',

        'clock_in_time',
        'clock_out_time',

        'clock_in_latitude',
        'clock_in_longitude',

        'clock_out_latitude',
        'clock_out_longitude',

        'status',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}