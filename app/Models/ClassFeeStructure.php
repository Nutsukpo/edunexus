<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassFeeStructure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_class_id',
        'academic_year_id',
        'fee_type',
        'fee_name',
        'amount',
        'description',
        'is_required',
        'is_active',
        'due_date',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'due_date' => 'date',
        'metadata' => 'array',
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

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('student_class_id', $classId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute()
    {
        return 'GHS ' . number_format($this->amount, 2);
    }

    public function getFeeTypeLabelAttribute()
    {
        $types = [
            'tuition' => 'Tuition Fee',
            'registration' => 'Registration Fee',
            'development' => 'Development Fee',
            'library' => 'Library Fee',
            'sports' => 'Sports Fee',
            'medical' => 'Medical Fee',
            'insurance' => 'Insurance Fee',
            'transport' => 'Transport Fee',
            'boarding' => 'Boarding Fee',
            'uniform' => 'Uniform Fee',
            'books' => 'Books Fee',
            'exam' => 'Examination Fee',
            'graduation' => 'Graduation Fee',
            'other' => 'Other Fee',
        ];
        return $types[$this->fee_type] ?? $this->fee_type;
    }
}