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
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(
            Department::class,
            'department_id'
        );
    }

    /**
     * User account associated with this staff member.
     *
     * users.email = staff.email
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'email',
            'email'
        );
    }

    /**
     * Lesson notes created by this staff member.
     *
     * NOTE:
     * The existing lesson_notes table may still use teacher_id.
     * We are preserving that existing relationship for compatibility.
     */
    public function lessonNotes()
    {
        return $this->hasMany(
            LessonNote::class,
            'teacher_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM MASTER ASSIGNMENTS
    |--------------------------------------------------------------------------
    |
    | student_class_staff
    |
    | A Form Master has full access to the assigned class,
    | subject to the application's module permissions.
    |
    */

    public function formMasterAssignments()
    {
        return $this->hasMany(
            StudentClassStaff::class,
            'staff_id'
        );
    }

    /**
     * Classes where this staff member is assigned as Form Master.
     */
    public function formMasterClasses()
    {
        return $this->belongsToMany(
            StudentClass::class,
            'student_class_staff',
            'staff_id',
            'student_class_id'
        )
        ->withPivot([
            'academic_year_id',
            'term_id',
        ])
        ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT TEACHER ASSIGNMENTS
    |--------------------------------------------------------------------------
    |
    | class_subject_staff
    |
    | A Subject Teacher gets:
    |
    | - Read access to the assigned class
    | - Write access only to the assigned subject
    |
    */

    public function subjectAssignments()
    {
        return $this->hasMany(
            ClassSubjectStaff::class,
            'staff_id'
        );
    }

    /**
     * Subjects this staff member teaches.
     *
     * The assignment is class-specific and period-specific.
     */
    public function assignedSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'class_subject_staff',
            'staff_id',
            'subject_id'
        )
        ->withPivot([
            'student_class_id',
            'academic_year_id',
            'term_id',
            'day',
            'start_time',
            'end_time',
            'room',
        ])
        ->withTimestamps();
    }

    /**
     * Classes where this staff member is assigned as a Subject Teacher.
     */
    public function assignedClasses()
    {
        return $this->belongsToMany(
            StudentClass::class,
            'class_subject_staff',
            'staff_id',
            'student_class_id'
        )
        ->withPivot([
            'subject_id',
            'academic_year_id',
            'term_id',
            'day',
            'start_time',
            'end_time',
            'room',
        ])
        ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | SALARY & PAYROLL
    |--------------------------------------------------------------------------
    */

    public function salaryStructure()
    {
        return $this->hasOne(SalaryStructure::class);
    }

    public function salaryStructures()
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function activeSalaryStructure()
    {
        return $this->hasOne(SalaryStructure::class)
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhere('status', 'active');
            })
            ->latest('effective_date');
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function createdPayrolls()
    {
        return $this->hasMany(
            PayrollPeriod::class,
            'created_by'
        );
    }

    public function approvedPayrolls()
    {
        return $this->hasMany(
            PayrollPeriod::class,
            'approved_by'
        );
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollPeriodStaff::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return trim(
            $this->first_name . ' ' . $this->last_name
        );
    }

    public function getDisplayNameAttribute()
    {
        $name = trim(
            $this->first_name . ' ' . $this->last_name
        );

        if ($this->other_name) {
            $name .= ' (' . $this->other_name . ')';
        }

        return $name;
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department
            ? $this->department->name
            : 'Not Assigned';
    }

    public function getStaffTypeLabelAttribute()
    {
        return [
            'teacher' => 'Teacher',
            'admin' => 'Administrator',
            'support' => 'Support Staff',
            'management' => 'Management',
            'other' => 'Other',
        ][$this->staff_type] ?? 'Not Specified';
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'active' => 'success',
            'inactive' => 'danger',
            'on_leave' => 'warning',
            'suspended' => 'secondary',
        ][$this->status] ?? 'secondary';
    }

    public function getInitialsAttribute()
    {
        return strtoupper(
            substr($this->first_name ?? '', 0, 1) .
            substr($this->last_name ?? '', 0, 1)
        );
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo
            ? asset('storage/staff/' . $this->photo)
            : asset('images/default-avatar.png');
    }

    public function getTotalPackageAttribute()
    {
        return ($this->salary ?? 0) + ($this->allowances ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTeachers($query)
    {
        return $query->where('staff_type', 'teacher');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('staff_type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('other_name', 'LIKE', "%{$search}%")
                ->orWhere('staff_id', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isTeacher(): bool
    {
        return $this->staff_type === 'teacher';
    }

    public function isActive(): bool
    {
        return strtolower((string) $this->status) === 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::created(function ($staff) {
            $staff->salaryStructure()->create([
                'basic_salary' => 0,
                'housing_allowance' => 0,
                'transport_allowance' => 0,
                'medical_allowance' => 0,
                'responsibility_allowance' => 0,
                'other_allowance' => 0,
                'tax' => 0,
                'ssnit' => 0,
                'tier2' => 0,
                'tier3' => 0,
                'loan_deduction' => 0,
                'other_deduction' => 0,
                'effective_date' => now(),
                'is_active' => true,
            ]);
        });
    }
}