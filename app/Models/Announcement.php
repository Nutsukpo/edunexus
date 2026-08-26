<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'audience',
        'priority',
        'publish_date',
        'expiry_date',
        'is_published',
        'is_featured',
        'image',
        'link',
        'created_by',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
        'expiry_date' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(function ($q) {
                         $q->whereNull('publish_date')
                           ->orWhere('publish_date', '<=', now());
                     })
                     ->where(function ($q) {
                         $q->whereNull('expiry_date')
                           ->orWhere('expiry_date', '>=', now());
                     });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now())
                     ->orWhere(function ($q) {
                         $q->where('is_published', true)
                           ->where('expiry_date', '<=', now());
                     });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForAudience($query, $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('audience', 'all')
              ->orWhere('audience', $audience);
        });
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    // Check if announcement is expired
    public function isExpired()
    {
        if (is_null($this->expiry_date)) {
            return false;
        }
        
        return $this->expiry_date->isPast();
    }

    // Check if announcement is published and not expired
    public function isActive()
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->publish_date && $this->publish_date->isFuture()) {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        return true;
    }

    // Check if announcement can be published
    public function canBePublished()
    {
        if ($this->isExpired()) {
            return false;
        }

        if ($this->publish_date && $this->publish_date->isFuture()) {
            return false;
        }

        return true;
    }

    // Accessors
    public function getPriorityColorAttribute()
    {
        return [
            'low' => 'secondary',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
        ][$this->priority] ?? 'secondary';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'general' => 'secondary',
            'academic' => 'info',
            'event' => 'success',
            'urgent' => 'danger',
            'exam' => 'warning',
        ];
        return $badges[$this->type] ?? 'secondary';
    }

    public function getAudienceBadgeAttribute()
    {
        $badges = [
            'all' => 'secondary',
            'students' => 'primary',
            'staff' => 'info',
            'parents' => 'success',
            'teachers' => 'warning',
        ];
        return $badges[$this->audience] ?? 'secondary';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->isExpired()) {
            return 'danger';
        }
        
        if (!$this->is_published) {
            return 'secondary';
        }
        
        return 'success';
    }

    public function getStatusTextAttribute()
    {
        if ($this->isExpired()) {
            return 'Expired';
        }
        
        if (!$this->is_published) {
            return 'Draft';
        }
        
        return 'Published';
    }
}