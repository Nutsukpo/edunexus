<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'student_invoice_id',
        'student_id',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'remarks',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(StudentInvoice::class, 'student_invoice_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Accessor for formatted amount
    public function getFormattedAmountAttribute()
    {
        return 'GH₵ ' . number_format($this->amount, 2);
    }
}