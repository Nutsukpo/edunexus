<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
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

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceSession::class,
            'attendance_session_id'
        );
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(
            AttendanceSession::class,
            'attendance_session_id'
        );
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(
            Student::class,
            'student_id'
        );
    }

    public function classAssignment(): BelongsTo
    {
        return $this->belongsTo(
            StudentClassAssignment::class,
            'student_class_assignment_id'
        );
    }

    public function studentClassAssignment(): BelongsTo
    {
        return $this->belongsTo(
            StudentClassAssignment::class,
            'student_class_assignment_id'
        );
    }
}