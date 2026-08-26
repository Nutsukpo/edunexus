<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrievanceComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'grievance_id',
        'staff_id',
        'comment',
        'is_internal',
        'attachment',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function grievance()
    {
        return $this->belongsTo(Grievance::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Accessors
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y h:i A') : 'N/A';
    }

    public function getStaffNameAttribute()
    {
        return $this->staff->full_name ?? 'Unknown Staff';
    }
}