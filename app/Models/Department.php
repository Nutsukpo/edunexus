<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'head_of_department',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Head of Department (Staff)
     */
    public function hod()
    {
        return $this->belongsTo(Staff::class, 'head_of_department');
    }

    /**
     * Staff members in this department
     */
    public function staff()
    {
        return $this->hasMany(Staff::class, 'department_id');
    }

    /**
     * Get staff count
     */
    public function getStaffCountAttribute()
    {
        return $this->staff()->count();
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}