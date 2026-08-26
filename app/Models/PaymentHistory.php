<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_item_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference'
    ];

    protected $casts = [
        'payment_date'=>'date'
    ];

    public function payrollItem()
    {
        return $this->belongsTo(PayrollItem::class);
    }
}