<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeReceipt extends Model
{
    protected $fillable = [
        'fee_payment_id',
        'receipt_number',
        'receipt_template',
        'receipt_data',
        'pdf_path',
        'generated_at',
    ];

    protected $casts = [
        'receipt_data' => 'array',
        'generated_at' => 'datetime',
    ];

    public function feePayment()
    {
        return $this->belongsTo(FeePayment::class);
    }
}