<?php
// app/Models/SchoolFeeStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolFeeStructure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_class_id',
        'academic_year_id',
        'fee_category_id',
        'name',
        'description',
        'amount',
        'is_optional',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the student class that this fee structure belongs to.
     */
    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    /**
     * Get the academic year that this fee structure belongs to.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the fee category that this fee structure belongs to.
     */
    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    /**
     * Get the student fee allocations for this fee structure.
     */
    public function studentFeeAllocations()
    {
        return $this->hasMany(StudentFeeAllocation::class);
    }

    /**
     * Scope a query to only include active fee structures.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include optional fee structures.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    /**
     * Scope a query to only include required fee structures.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_optional', false);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get optional badge class.
     */
    public function getOptionalBadgeAttribute()
    {
        return $this->is_optional ? 'badge-warning' : 'badge-primary';
    }

    /**
     * Get optional label.
     */
    public function getOptionalLabelAttribute()
    {
        return $this->is_optional ? 'Optional' : 'Required';
    }
}