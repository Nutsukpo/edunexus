<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [

        'attendance_session_id',

        'student_class_assignment_id',

        'student_id',

        'status',

        'remarks',

        'check_in_time',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Attendance Session
     */
    public function attendanceSession()
    {
        return $this->belongsTo(
            AttendanceSession::class
        );
    }

    /**
     * Student
     */
    public function student()
    {
        return $this->belongsTo(
            Student::class
        );
    }

    /**
     * Student Class Assignment
     */
    public function studentClassAssignment()
    {
        return $this->belongsTo(
            StudentClassAssignment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Status Badge Color
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {

            'present' => 'success',

            'absent'  => 'danger',

            'late'    => 'warning',

            'excused' => 'info',

            default   => 'secondary',
        };
    }
}