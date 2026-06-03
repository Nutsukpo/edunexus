<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'student_class_id',
        'academic_year_id',
        'status',
        'assigned_date',
        'promotion_date',
        'is_current',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}