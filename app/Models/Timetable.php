<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timetable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'student_class_id',
        'uploaded_by',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
