<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrievanceCategory extends Model
{
    use HasFactory;

    // IMPORTANT: Specify the correct table name
    protected $table = 'students_grievance_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'priority',
        'is_active',
        'created_by',
    ];

    /**
     * Relationships
     */
    public function grievances()
    {
        return $this->hasMany(StudentGrievance::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessors
     */
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
}