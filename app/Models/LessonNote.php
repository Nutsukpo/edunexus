<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'note_code',
        'type',
        'staff_id',
        'student_class_id',
        'subject_id',
        'academic_year_id',
        'term_id',
        'topic',
        'sub_topic',
        'description',
        'content',
        'lesson_date',
        'start_time',
        'end_time',
        'duration',
        'attachment',
        'attachments',
        'resources',
        'learning_objectives',
        'learning_outcomes',
        'delivery_method',
        'teaching_aids',
        'assessment_methods',
        'homework',
        'remarks',
        'challenges',
        'recommendations',
        'expected_students',
        'actual_students',
        'student_participation',
        'comment',
        'comments',
        'commented_by',
        'status',
        // New approval fields
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'feedback_requested_at',
        'feedback_requested_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'resources' => 'array',
        'learning_objectives' => 'array',
        'learning_outcomes' => 'array',
        'teaching_aids' => 'array',
        'assessment_methods' => 'array',
        'student_participation' => 'array',
        'comments' => 'array',
        'lesson_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'feedback_requested_at' => 'datetime',
    ];

    // Relationships
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function commentedBy()
    {
        return $this->belongsTo(Staff::class, 'commented_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(Staff::class, 'rejected_by');
    }

    // Scopes
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('student_class_id', $classId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('lesson_date', [$startDate, $endDate]);
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

    // Accessors and Mutators
    public function getFullTopicAttribute()
    {
        return $this->sub_topic 
            ? "{$this->topic} - {$this->sub_topic}" 
            : $this->topic;
    }

    public function getStatusBadgeAttribute()
    {
        $statusColors = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'published' => 'success',
            'approved' => 'success',
            'rejected' => 'danger',
            'archived' => 'danger',
        ];

        return $statusColors[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'published' => 'Published',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'archived' => 'Archived',
        ];

        return $statusLabels[$this->status] ?? 'Unknown';
    }

    // Helper Methods
    public function generateNoteCode(): string
    {
        $prefix = 'LN';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        return "{$prefix}-{$date}-{$random}";
    }

    public function hasAttachment(): bool
    {
        return !is_null($this->attachment) || !is_null($this->attachments);
    }

    public function getStudentCount(): array
    {
        return [
            'expected' => $this->expected_students,
            'actual' => $this->actual_students,
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft' || is_null($this->status);
    }

    public function canBeApproved(): bool
    {
        return $this->isPending() || $this->isDraft();
    }
}