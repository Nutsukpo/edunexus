<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Leave extends Model
{

    protected $fillable = [
        'full_name',
        'designation',
        'contact_number',
        'leave_type',
        'reason',
        'date_commencement',
        'date_resumption',
        'days_applied_for',
        'date_of_application',
        'date_last_leave',
        'days_entitled',
        'days_already_utilized',
        'signature',
        'status',
        'days_granted',
        'recommendation',
        'respect_of',
        'administrator_name',
        'administrator_signature',
        'administrator_date',
        'zonal_coordinator_name',
        'zonal_coordinator_signature',
        'zonal_coordinator_date',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'cancelled_at',
        'cancelled_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_commencement' => 'date',
        'date_resumption' => 'date',
        'date_of_application' => 'date',
        'date_last_leave' => 'date',
        'administrator_date' => 'date',
        'zonal_coordinator_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}