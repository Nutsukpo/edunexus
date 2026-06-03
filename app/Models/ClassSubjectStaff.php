<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubjectStaff extends Model
{
    protected $fillable = [
        'student_class_id',
        'subject_id',
        'staff_id',
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

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}