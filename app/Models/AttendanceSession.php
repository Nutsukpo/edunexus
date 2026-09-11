<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = [
        'student_class_id',
        'academic_year_id',
        'term_id',
        'attendance_date',
        'taken_by',
        'remarks',
        'status',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | CLASS
    |--------------------------------------------------------------------------
    */

    public function studentClass(): BelongsTo
    {
        return $this->belongsTo(
            StudentClass::class,
            'student_class_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TERM
    |--------------------------------------------------------------------------
    */

    public function term(): BelongsTo
    {
        return $this->belongsTo(
            Term::class,
            'term_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF WHO TOOK ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'taken_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE RECORDS
    |--------------------------------------------------------------------------
    */

    public function attendances(): HasMany
    {
        return $this->hasMany(
            Attendance::class,
            'attendance_session_id'
        );
    }
}