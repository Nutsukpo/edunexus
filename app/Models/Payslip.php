<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    // Add status constants
    const STATUS_GENERATED = 'generated';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'staff_id',
        'payroll_period_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'bonus',
        'overtime',
        'total_earnings',
        'tax',
        'pension',
        'tier2',
        'tier3',
        'insurance',
        'loans',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'notes',
        'breakdown',
        'created_by',
        'status'
    ];

    protected $casts = [
        'breakdown' => 'array',
        'month' => 'integer',
        'year' => 'integer',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonus' => 'decimal:2',
        'overtime' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'tax' => 'decimal:2',
        'pension' => 'decimal:2',
        'tier2' => 'decimal:2',
        'tier3' => 'decimal:2',
        'insurance' => 'decimal:2',
        'loans' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 10));
    }

    public function getFormattedNetPayAttribute()
    {
        return number_format($this->net_pay, 2);
    }

    public function getFormattedBasicSalaryAttribute()
    {
        return number_format($this->basic_salary, 2);
    }

    public function getFormattedTotalEarningsAttribute()
    {
        return number_format($this->total_earnings, 2);
    }

    public function getFormattedTotalDeductionsAttribute()
    {
        return number_format($this->total_deductions, 2);
    }

    public function scopeGenerated($query)
    {
        return $query->where('status', self::STATUS_GENERATED);
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeForPeriod($query, $payrollPeriodId)
    {
        return $query->where('payroll_period_id', $payrollPeriodId);
    }
}