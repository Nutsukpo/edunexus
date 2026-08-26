<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_item_id',
        'type',
        'amount',
        'reason'
    ];

    public function payrollItem()
    {
        return $this->belongsTo(PayrollItem::class);
    }
}