<?php
// app/Models/FeeCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'description', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function feeItems()
    {
        return $this->hasMany(FeeItem::class);
    }

    public function feeStructures()
    {
        return $this->belongsToMany(FeeStructure::class, 'fee_items');
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordered categories
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}