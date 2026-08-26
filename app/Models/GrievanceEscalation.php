<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrievanceEscalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'grievance_id',
        'from_staff_id',
        'to_staff_id',
        'reason',
        'level',
        'escalation_date',
        'response_deadline',
        'status',
    ];

    protected $casts = [
        'escalation_date' => 'date',
        'response_deadline' => 'date',
    ];

    /**
     * Relationships
     */
    public function grievance()
    {
        return $this->belongsTo(Grievance::class);
    }

    public function fromStaff()
    {
        return $this->belongsTo(Staff::class, 'from_staff_id');
    }

    public function toStaff()
    {
        return $this->belongsTo(Staff::class, 'to_staff_id');
    }

    /**
     * Accessors
     */
    public function getLevelLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->level));
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'acknowledged' => 'info',
            'responded' => 'primary',
            'resolved' => 'success',
        ];
        return $colors[$this->status] ?? 'secondary';
    }
}