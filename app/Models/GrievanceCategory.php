<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrievanceCategory extends Model
{
    use HasFactory;

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
        return $this->hasMany(Grievance::class);
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

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
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

    /**
     * Helper Methods
     */
    public function generateSlug()
    {
        return \Str::slug($this->name);
    }
}