<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'attendance_session_id',
        'student_class_assignment_id',
        'student_id',
        'status',
        'check_in_time',
        'remarks',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classAssignment()
    {
        return $this->belongsTo(
            StudentClassAssignment::class,
            'student_class_assignment_id'
        );
    }
}