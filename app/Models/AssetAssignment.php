<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'assigned_to',
        'assigned_by',
        'returned_to',
        'assigned_date',
        'expected_return_date',
        'actual_return_date',
        'assignment_notes',
        'return_notes',
        'status',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignor()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function returnedTo()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                    ->whereNotNull('expected_return_date')
                    ->where('expected_return_date', '<', now());
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'active' 
            && $this->expected_return_date 
            && $this->expected_return_date->lessThan(now());
    }
}