<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [

        'latitude',
        'longitude',
        'radius',

        'clock_in_start',
        'clock_in_end',

        'clock_out_start',
        'clock_out_end',

        'gps_enabled',
    ];
}