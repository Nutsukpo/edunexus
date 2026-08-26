<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillSheetItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bill_sheet_id',
        'fee_category_id',
        'name',
        'amount',
        'quantity',
        'total_amount',
        'is_optional',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'total_amount' => 'decimal:2',
        'is_optional' => 'boolean',
    ];

    public function billSheet()
    {
        return $this->belongsTo(BillSheet::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }
}