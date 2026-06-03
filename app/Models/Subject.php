<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [

        'name',
        'code',
        'description',
        'education_level',
        'staff_id',
        'category',
        'is_active',

    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function studentClasses()
    {
        return $this->belongsToMany(
            StudentClass::class,
            'student_class_subject'
        );
    }

    public function classStaffAssignments()
    {
        return $this->hasMany(ClassSubjectStaff::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

        // In app/Models/Subject.php
    public function classes()
    {
        return $this->belongsToMany(StudentClass::class, 'class_subjects')
                    ->withTimestamps();
    }
}