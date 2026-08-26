<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $fillable = [
        'fee_payment_id',
        'bill_sheet_item_id',
        'item_name',
        'original_amount',
        'paid_amount',
        'balance',
        'notes',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function feePayment()
    {
        return $this->belongsTo(FeePayment::class);
    }

    public function billSheetItem()
    {
        return $this->belongsTo(BillSheetItem::class);
    }
}