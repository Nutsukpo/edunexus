<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGrievanceComment extends Model
{
    use HasFactory, SoftDeletes;

    // Specify the correct table name
    protected $table = 'students_grievance_comments';

    protected $fillable = [
        'grievance_id',
        'staff_id',
        'student_id',
        'comment',
        'is_internal',
        'attachment',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
    ];

    public function grievance()
    {
        return $this->belongsTo(StudentGrievance::class, 'grievance_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getAuthorNameAttribute()
    {
        if ($this->staff_id) {
            return $this->staff->full_name ?? 'Unknown Staff';
        }
        if ($this->student_id) {
            return $this->student->full_name ?? 'Unknown Student';
        }
        return 'Unknown';
    }

    public function getAuthorTypeAttribute()
    {
        if ($this->staff_id) {
            return 'Staff';
        }
        if ($this->student_id) {
            return 'Student';
        }
        return 'Unknown';
    }
}