<?php
// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $table = 'students';

    protected $fillable = [
        'user_id',
        'admission_number',
        'student_id',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'address',
        'phone',
        'email',
        'password',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'is_active',
        'current_class_id',
        'portal_access',
        'last_login_at',
        'password_changed',
        'nationality',
        'religion',
        'has_disability',
        'disability_type',
        'father_name',
        'father_phone',
        'father_email',
        'father_occupation',
        'mother_name',
        'mother_phone',
        'mother_email',
        'mother_occupation',
        'admission_date',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
        'portal_access' => 'boolean',
        'password_changed' => 'boolean',
        'last_login_at' => 'datetime',
        'has_disability' => 'boolean',
        'admission_date' => 'date',
    ];

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the full name with admission number.
     */
    public function getFullNameWithAdmissionAttribute()
    {
        return $this->full_name . ' (' . ($this->admission_number ?? $this->student_id ?? 'N/A') . ')';
    }

    /**
     * Get the user associated with the student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current class of the student.
     * NOTE: This is the relationship name used in your model
     */
    public function currentClass()
    {
        return $this->belongsTo(StudentClass::class, 'current_class_id');
    }

    /**
     * Alias for currentClass() to match the naming convention used in other parts of the app
     * This allows you to use either $student->studentClass or $student->currentClass
     */
    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'current_class_id');
    }

    /**
     * Get all class assignments for the student.
     * This is the relationship needed for the BillSheetController
     */
    public function studentClassAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class, 'student_id');
    }

    /**
     * Alias for studentClassAssignments() to maintain backward compatibility
     */
    public function classAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class, 'student_id');
    }

    /**
     * Get the current active class assignment.
     */
    public function currentClassAssignment()
    {
        return $this->hasOne(StudentClassAssignment::class, 'student_id')
            ->where('is_current', true)
            ->where('status', 'active');
    }

    /**
     * Get the student results.
     */
    public function studentResults()
    {
        return $this->hasMany(StudentResult::class, 'student_id');
    }

    /**
     * Get results for a specific academic year and term.
     */
    public function resultsFor($academicYearId, $termId)
    {
        return $this->studentResults()
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->get();
    }

    /**
     * Get the fee allocations for the student.
     */
    public function feeAllocations()
    {
        return $this->hasMany(StudentFeeAllocation::class);
    }

    /**
     * Get the payments for the student.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the fee payments for the student.
     */
    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Get the bills for the student.
     */
    public function bills()
    {
        return $this->hasMany(BillSheet::class);
    }

    /**
     * Get the student fee accounts.
     */
    public function feeAccounts()
    {
        return $this->hasMany(StudentFeeAccount::class);
    }

    /**
     * Get the student fee accounts for the current academic year.
     */
    public function currentFeeAccount()
    {
        return $this->hasOne(StudentFeeAccount::class)
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->latest();
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get students by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('current_class_id', $classId);
    }

    /**
     * Scope a query to get students with active assignments.
     */
    public function scopeWithActiveAssignments($query)
    {
        return $query->whereHas('studentClassAssignments', function($q) {
            $q->where('is_current', true)->where('status', 'active');
        });
    }

    /**
     * Scope a query to search students by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('admission_number', 'LIKE', "%{$search}%")
                ->orWhere('student_id', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Get the name for the authentication guard.
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

        
}