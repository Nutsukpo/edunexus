<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFeeItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_fee_account_id',
        'class_fee_structure_id',
        'fee_type',
        'fee_name',
        'amount',
        'description',
        'is_required',
        'due_date',
        'status',
        'paid_amount',
        'remaining_amount',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'is_required' => 'boolean',
        'due_date' => 'date',
        'metadata' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function studentFeeAccount()
    {
        return $this->belongsTo(StudentFeeAccount::class);
    }

    public function classFeeStructure()
    {
        return $this->belongsTo(ClassFeeStructure::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }
}