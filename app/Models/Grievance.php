<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grievance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'grievance_code',
        'title',
        'description',
        'staff_id',
        'category_id',
        'assigned_to',
        'department_id',
        'priority',
        'status',
        'attachment',
        'attachments',
        'submission_date',
        'review_date',
        'resolution_date',
        'closure_date',
        'appeal_deadline',
        'additional_details',
        'is_confidential',
        'is_anonymous',
        'remarks',
    ];

    protected $casts = [
        'attachments' => 'array',
        'additional_details' => 'array',
        'submission_date' => 'date',
        'review_date' => 'date',
        'resolution_date' => 'date',
        'closure_date' => 'date',
        'appeal_deadline' => 'date',
        'is_confidential' => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function category()
    {
        return $this->belongsTo(GrievanceCategory::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function comments()
    {
        return $this->hasMany(GrievanceComment::class);
    }

    public function histories()
    {
        return $this->hasMany(GrievanceHistory::class);
    }

    public function escalations()
    {
        return $this->hasMany(GrievanceEscalation::class);
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeByAssignedTo($query, $staffId)
    {
        return $query->where('assigned_to', $staffId);
    }

    public function scopePending($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed', 'rejected']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    /**
     * Accessors
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'under_review' => 'primary',
            'investigation' => 'warning',
            'resolution_proposed' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary',
            'rejected' => 'danger',
            'appealed' => 'warning',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getPriorityBadgeAttribute()
    {
        $colors = [
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'dark',
        ];
        return $colors[$this->priority] ?? 'secondary';
    }

    public function getPriorityLabelAttribute()
    {
        return ucfirst($this->priority);
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y h:i A') : 'N/A';
    }

    /**
     * Helper Methods
     */
    public function generateGrievanceCode()
    {
        $prefix = 'GRV';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));
        return "{$prefix}-{$date}-{$random}";
    }

    public function canEdit()
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    public function canDelete()
    {
        return in_array($this->status, ['draft', 'submitted', 'rejected']);
    }

    public function canAppeal()
    {
        return $this->status === 'rejected' && $this->appeal_deadline && now()->lte($this->appeal_deadline);
    }

    public function addHistory($action, $description, $oldValues = null, $newValues = null)
    {
        return $this->histories()->create([
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'performed_by' => auth()->user()->staff_id ?? null,
        ]);
    }

    public function escalate($toStaffId, $reason, $level)
    {
        return $this->escalations()->create([
            'from_staff_id' => $this->assigned_to,
            'to_staff_id' => $toStaffId,
            'reason' => $reason,
            'level' => $level,
            'escalation_date' => now(),
            'response_deadline' => now()->addDays(7),
            'status' => 'pending',
        ]);
    }
}