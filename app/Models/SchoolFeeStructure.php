<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolFeeStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'school_fee_structures';

    protected $fillable = [
        'name',
        'code',
        'academic_year_id',
        'term_id',
        'student_class_id',
        'fee_category_id',
        'amount',
        'fee_type',
        'payment_frequency',
        'description',
        'due_date',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'GHS ' . number_format($this->amount, 2);
    }

    public function getFeeTypeLabelAttribute()
    {
        return ucfirst($this->fee_type);
    }

    public function getPaymentFrequencyLabelAttribute()
    {
        return ucfirst(str_replace('-', ' ', $this->payment_frequency));
    }
}