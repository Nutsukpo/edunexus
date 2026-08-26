<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillSheet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'student_class_assignment_id',
        'academic_year_id',
        'term_id',
        'generated_date',
        'due_date',
        'description',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'status',
        'generated_by',
        'approved_by',
        'approved_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'generated_date'  => 'date',
        'due_date'        => 'date',
        'approved_at'     => 'datetime',

        'total_amount'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'net_amount'      => 'decimal:2',

        'is_active'       => 'boolean',
        'metadata'        => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | STUDENT CLASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    public function studentClassAssignment()
    {
        return $this->belongsTo(
            StudentClassAssignment::class,
            'student_class_assignment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->hasOneThrough(
            Student::class,
            StudentClassAssignment::class,
            'id',
            'id',
            'student_class_assignment_id',
            'student_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT CLASS
    |--------------------------------------------------------------------------
    */

    public function studentClass()
    {
        return $this->hasOneThrough(
            StudentClass::class,
            StudentClassAssignment::class,
            'id',
            'id',
            'student_class_assignment_id',
            'student_class_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    public function academicYear()
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

    public function term()
    {
        return $this->belongsTo(
            Term::class,
            'term_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ITEMS
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(
            BillSheetItem::class,
            'bill_sheet_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */

    public function generatedBy()
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeForAssignment($query, $assignmentId)
    {
        return $query->where(
            'student_class_assignment_id',
            $assignmentId
        );
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->whereHas(
            'studentClassAssignment',
            fn ($q) => $q->where('student_id', $studentId)
        );
    }

    public function scopeForClass($query, $classId)
    {
        return $query->whereHas(
            'studentClassAssignment',
            fn ($q) => $q->where('student_class_id', $classId)
        );
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where(
            'academic_year_id',
            $academicYearId
        );
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where(
            'term_id',
            $termId
        );
    }

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY HELPERS
    |--------------------------------------------------------------------------
    */

    public function getStudentNameAttribute(): string
    {
        $student = $this->studentClassAssignment?->student;

        if (!$student) {
            return 'N/A';
        }

        return trim(
            collect([
                $student->first_name ?? null,
                $student->middle_name ?? null,
                $student->last_name ?? null,
            ])
                ->filter()
                ->implode(' ')
        );
    }

    public function getClassNameAttribute(): string
    {
        return $this->studentClassAssignment?->studentClass?->name
            ?? 'N/A';
    }

    public function getAcademicYearNameAttribute(): string
    {
        return $this->academicYear?->name ?? 'N/A';
    }

    public function getTermNameAttribute(): string
    {
        return $this->term?->name ?? 'N/A';
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'GHS ' . number_format(
            (float) $this->total_amount,
            2
        );
    }

    public function getFormattedNetAmountAttribute(): string
    {
        return 'GHS ' . number_format(
            (float) $this->net_amount,
            2
        );
    }
}