<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DiscussionGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'avatar',
        'type',
        'created_by',
        'updated_by',
        'is_active',
        'last_message_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name . '-' . Str::random(6));
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    // FIX: Use the correct foreign key column name
    public function participants()
    {
        return $this->hasMany(DiscussionParticipant::class, 'group_id');
    }

    public function messages()
    {
        return $this->hasMany(DiscussionMessage::class, 'group_id');
    }

    public function latestMessages()
    {
        return $this->hasMany(DiscussionMessage::class, 'group_id')->latest();
    }

    public function latestMessage()
    {
        return $this->hasOne(DiscussionMessage::class, 'group_id')->latest();
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&size=100';
    }

    public function getParticipantCountAttribute()
    {
        return $this->participants()->count();
    }

    public function addParticipant($staffId, $role = 'member')
    {
        return $this->participants()->create([
            'staff_id' => $staffId,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function removeParticipant($staffId)
    {
        return $this->participants()->where('staff_id', $staffId)->delete();
    }

    public function isParticipant($staffId)
    {
        return $this->participants()->where('staff_id', $staffId)->exists();
    }

    public function getUnreadCount($staffId)
    {
        $lastRead = $this->participants()
            ->where('staff_id', $staffId)
            ->value('last_read_at');

        if (!$lastRead) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $lastRead)
            ->count();
    }
}