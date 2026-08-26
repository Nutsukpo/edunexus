<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructureItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fee_structure_id',
        'fee_category_id',
        'name',
        'description',
        'amount',
        'is_optional',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_active' => 'boolean',
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