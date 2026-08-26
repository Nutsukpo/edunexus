<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_class_id',
        'academic_year_id',
        'term_id',
        'fee_type',
        'fee_category',
        'amount',
        'is_optional',
        'is_active',
        'description',
        'due_date',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean',
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

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('student_class_id', $classId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_optional', false);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
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
            'examination' => 'Examination Fee',
            'library' => 'Library Fee',
            'sports' => 'Sports Fee',
            'development' => 'Development Fee',
            'transport' => 'Transport Fee',
            'boarding' => 'Boarding Fee',
            'uniform' => 'Uniform Fee',
            'books' => 'Books Fee',
            'lab' => 'Laboratory Fee',
            'computer' => 'Computer Fee',
            'health' => 'Health Fee',
            'insurance' => 'Insurance Fee',
            'other' => 'Other',
        ];

        return $types[$this->fee_type] ?? $this->fee_type;
    }

    public function getFeeCategoryLabelAttribute()
    {
        $categories = [
            'academic' => 'Academic',
            'developmental' => 'Developmental',
            'extra_curricular' => 'Extra Curricular',
            'facility' => 'Facility',
            'other' => 'Other',
        ];

        return $categories[$this->fee_category] ?? $this->fee_category;
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get total fee amount for a specific class and academic year
     */
    public static function getTotalFeesForClass($classId, $academicYearId, $termId = null)
    {
        $query = self::active()
            ->forClass($classId)
            ->forAcademicYear($academicYearId);

        if ($termId) {
            $query->forTerm($termId);
        }

        return $query->sum('amount');
    }

    /**
     * Get fee structure with breakdown for a class
     */
    public static function getFeeBreakdownForClass($classId, $academicYearId, $termId = null)
    {
        $query = self::with(['studentClass', 'academicYear', 'term'])
            ->active()
            ->forClass($classId)
            ->forAcademicYear($academicYearId);

        if ($termId) {
            $query->forTerm($termId);
        }

        return $query->orderBy('fee_type')->get();
    }
}