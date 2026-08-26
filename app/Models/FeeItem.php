<?php
// app/Models/FeeItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeItem extends Model
{
    protected $fillable = [
        'fee_structure_id', 'fee_category_id', 'name', 'description', 
        'amount', 'is_optional', 'is_required', 'sort_order'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }
}