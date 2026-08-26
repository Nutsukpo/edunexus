<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GraduatedStudent extends Model
{
    protected $table = 'graduated_students';
    
    protected $fillable = [
        'student_id',
        'student_class_id',  // NOT class_id
        'academic_year_id',
        'term_id',
        'graduation_date',
        'graduation_type',
        'remarks'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
}