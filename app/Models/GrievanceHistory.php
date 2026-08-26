<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrievanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'grievance_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'performed_by',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relationships
     */
    public function grievance()
    {
        return $this->belongsTo(Grievance::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(Staff::class, 'performed_by');
    }

    /**
     * Accessors
     */
    public function getActionLabelAttribute()
    {
        return ucfirst($this->action);
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y h:i A') : 'N/A';
    }
}