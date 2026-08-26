<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    protected $fillable = [
        'staff_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'medical_allowance',
        'responsibility_allowance',
        'other_allowance',
        'tax',
        'ssnit',
        'tier2',
        'tier3',
        'loan_deduction',
        'other_deduction',
        'effective_date',
        'is_active'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Get the staff that owns the salary structure.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Calculate total allowances
     */
    public function getTotalAllowanceAttribute()
    {
        return ($this->housing_allowance ?? 0) + 
               ($this->transport_allowance ?? 0) + 
               ($this->medical_allowance ?? 0) + 
               ($this->responsibility_allowance ?? 0) + 
               ($this->other_allowance ?? 0);
    }

    /**
     * Calculate total deductions
     */
    public function getTotalDeductionAttribute()
    {
        return ($this->tax ?? 0) + 
               ($this->ssnit ?? 0) + 
               ($this->tier2 ?? 0) + 
               ($this->tier3 ?? 0) + 
               ($this->loan_deduction ?? 0) + 
               ($this->other_deduction ?? 0);
    }

    /**
     * Calculate net salary
     */
    public function getNetSalaryAttribute()
    {
        return ($this->basic_salary ?? 0) + $this->total_allowance - $this->total_deduction;
    }

    /**
     * Get formatted basic salary
     */
    public function getFormattedBasicSalaryAttribute()
    {
        return number_format($this->basic_salary, 2);
    }

    /**
     * Get formatted total allowance
     */
    public function getFormattedTotalAllowanceAttribute()
    {
        return number_format($this->total_allowance, 2);
    }

    /**
     * Get formatted total deduction
     */
    public function getFormattedTotalDeductionAttribute()
    {
        return number_format($this->total_deduction, 2);
    }

    /**
     * Get formatted net salary
     */
    public function getFormattedNetSalaryAttribute()
    {
        return number_format($this->net_salary, 2);
    }

    /**
     * Scope a query to only include active structures.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive structures.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}