<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * Spatie Permission uses the web guard for EDUNEXUS users.
     */
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'status',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([
            'Super Admin',
            'Administrator',
        ]);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole([
            'Teaching Staff',
            'Non-Teaching Staff',
            'MIS',
            'Power User',
            'Accountant',
            'Administrator',
            'Super Admin',
        ]);
    }

    public function isActive(): bool
    {
        return strtolower((string) $this->status) === 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | The current users table has no staff_id column.
    | The current staff table has no user_id column.
    |
    | Therefore the existing database links the two records through email.
    |
    */

    public function staff()
    {
        return $this->hasOne(
            Staff::class,
            'email',
            'email'
        );
    }
}