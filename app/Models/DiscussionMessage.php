<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'sender_id',
        'parent_id',
        'message',
        'type',
        'metadata',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
        'read_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_by' => 'array',
        'is_edited' => 'boolean',
        'is_deleted' => 'boolean',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(DiscussionGroup::class, 'group_id');
    }

    public function sender()
    {
        return $this->belongsTo(Staff::class, 'sender_id');
    }

    public function parent()
    {
        return $this->belongsTo(DiscussionMessage::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(DiscussionMessage::class, 'parent_id');
    }

    public function attachments()
    {
        return $this->hasMany(DiscussionAttachment::class, 'message_id');
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    public function markAsRead($staffId)
    {
        $readBy = $this->read_by ?? [];
        if (!in_array($staffId, $readBy)) {
            $readBy[] = $staffId;
            $this->update(['read_by' => $readBy]);
        }
    }

    public function isReadBy($staffId)
    {
        return in_array($staffId, $this->read_by ?? []);
    }

    public function getReadCountAttribute()
    {
        return count($this->read_by ?? []);
    }
}