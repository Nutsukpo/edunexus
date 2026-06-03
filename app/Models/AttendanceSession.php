<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'student_class_id',
        'attendance_date',
        'taken_by',
        'remarks',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    
}