<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'staff_id',
        'first_name',
        'last_name',
        'other_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'department',
        'department_id',
        'position',
        'date_employed',
        'salary',
        'staff_type',
        'status',
        'photo',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_employed' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Department relationship
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get staff display name
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        if ($this->other_name) {
            $name .= ' (' . $this->other_name . ')';
        }
        return $name;
    }

        /**
     * Get department name attribute
     */
    public function getDepartmentNameAttribute()
    {
        if ($this->department) {
            return $this->department->name;
        }
        return 'Not Assigned';
    }
}