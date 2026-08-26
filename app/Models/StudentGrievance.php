<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGrievance extends Model
{
    use HasFactory, SoftDeletes;

    // Specify the correct table name
    protected $table = 'students_grievances';

    protected $fillable = [
        'grievance_code',
        'title',
        'description',
        'student_id',
        'category_id',
        'assigned_to',
        'class_id',
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
        'staff_response',
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
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(StudentGrievanceCategory::class, 'category_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function comments()
    {
        return $this->hasMany(StudentGrievanceComment::class, 'grievance_id');
    }

    public function histories()
    {
        return $this->hasMany(StudentGrievanceHistory::class, 'grievance_id');
    }

    public function escalations()
    {
        return $this->hasMany(StudentGrievanceEscalation::class, 'grievance_id');
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

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopePending($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed', 'rejected']);
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

    /**
     * Helper Methods
     */
    public function generateGrievanceCode()
    {
        $prefix = 'SGRV';
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

    public function addHistory($action, $description, $oldValues = null, $newValues = null, $performerType = 'staff')
    {
        // Get the authenticated user's ID based on role
        $performedBy = null;
        if (auth()->check()) {
            if (auth()->user()->role === 'student' && auth()->user()->student_id) {
                $performedBy = auth()->user()->student_id;
            } elseif (auth()->user()->staff_id) {
                $performedBy = auth()->user()->staff_id;
            }
        }

        return $this->histories()->create([
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'performed_by' => $performedBy,
            'performer_type' => $performerType,
        ]);
    }
}