<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgression extends Model
{
    protected $table = 'student_progressions';
    
    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'action',
        'remarks',
        'processed_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(StudentClass::class, 'to_class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}