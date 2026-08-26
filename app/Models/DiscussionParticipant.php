<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'staff_id',
        'role',
        'joined_at',
        'last_read_at',
        'is_active',
        'is_muted',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
        'is_active' => 'boolean',
        'is_muted' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(DiscussionGroup::class, 'group_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}