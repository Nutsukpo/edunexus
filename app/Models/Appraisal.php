<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffAppraisal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'staff_id',
        'academic_year_id',
        'term_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'file_mime',
        'submission_date',
        'status',
        'reviewer_comments',
        'reviewed_by',
        'reviewed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'reviewed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // Relationships
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Staff::class, 'reviewed_by');
    }

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Staff::class, 'updated_by');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
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

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'reviewed' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $this->file_size;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute()
    {
        $types = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'ppt' => 'fa-file-powerpoint text-warning',
            'pptx' => 'fa-file-powerpoint text-warning',
            'jpg' => 'fa-file-image text-warning',
            'jpeg' => 'fa-file-image text-warning',
            'png' => 'fa-file-image text-warning',
            'gif' => 'fa-file-image text-warning',
            'zip' => 'fa-file-archive text-secondary',
            'rar' => 'fa-file-archive text-secondary',
        ];
        return $types[$this->file_type] ?? 'fa-file text-info';
    }

    // Helper Methods
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function submit()
    {
        $this->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);
    }

    public function review($reviewerId, $comments = null)
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_comments' => $comments,
        ]);
    }

    public function approve($reviewerId, $comments = null)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_comments' => $comments,
        ]);
    }

    public function reject($reviewerId, $comments = null)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'reviewer_comments' => $comments,
        ]);
    }
}