<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Staff;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role', // kept for backward compatibility; Spatie role is authoritative
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

    /**
     * Spatie is the authoritative RBAC system.
     * These legacy helpers remain for compatibility with older code.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Super Admin') || $this->hasRole('Administrator');
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
        return $this->status === 'active';
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * A user account may have one linked staff record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
