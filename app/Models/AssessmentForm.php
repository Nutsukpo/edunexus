<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'staff_id',
        'student_class_id',
        'academic_year_id',
        'term_id',
        'subject_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'file_mime',
        'assessment_date',
        'due_date',
        'status',
        'assessment_type',
        'metadata',
        'downloads_count',
        'views_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'assessment_date' => 'date',
        'due_date' => 'date',
        'file_size' => 'integer',
        'downloads_count' => 'integer',
        'views_count' => 'integer',
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

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
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
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('student_class_id', $classId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'published' => 'success',
            'archived' => 'dark',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getAssessmentTypeBadgeAttribute()
    {
        $badges = [
            'quiz' => 'info',
            'test' => 'primary',
            'exam' => 'danger',
            'assignment' => 'warning',
            'project' => 'success',
        ];
        return $badges[$this->assessment_type] ?? 'secondary';
    }

    public function getFileIconAttribute()
    {
        $types = [
            'pdf' => 'fa-file-pdf text-danger',
            'jpg' => 'fa-file-image text-warning',
            'jpeg' => 'fa-file-image text-warning',
            'png' => 'fa-file-image text-warning',
            'gif' => 'fa-file-image text-warning',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
        ];
        return $types[$this->file_type] ?? 'fa-file text-info';
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'N/A';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($this->file_size >= 1024 && $i < count($units) - 1) {
            $this->file_size /= 1024;
            $i++;
        }
        return round($this->file_size, 2) . ' ' . $units[$i];
    }

    // Helper Methods
    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function publish()
    {
        $this->update(['status' => 'published']);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementDownloads()
    {
        $this->increment('downloads_count');
    }
}