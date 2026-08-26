<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'student_class_code',
        'class_prefect_id',
        'name',
        'education_type',
        'class_type',
        'stream',
        'staff_id',
        'capacity',
        'date_of_birth',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the academic year this class belongs to.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the class teacher (staff).
     */
    public function classTeacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the class prefect (student).
     */
    public function classPrefect()
    {
        return $this->belongsTo(Student::class, 'class_prefect_id');
    }

    /**
     * Get enrollments for this class.
     */
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Get student class assignments (NEW - for StudentClassAssignment model).
     */
    public function studentClassAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class, 'student_class_id');
    }

    /**
     * Get active student class assignments.
     */
    public function activeStudentClassAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class, 'student_class_id')
            ->where('is_current', true)
            ->where('status', 'active');
    }

    /**
     * Get students through student class assignments (NEW).
     */
    public function assignedStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            StudentClassAssignment::class,
            'student_class_id', // Foreign key on student_class_assignments table
            'id', // Foreign key on students table
            'id', // Local key on student_classes table
            'student_id' // Local key on student_class_assignments table
        )->where('student_class_assignments.is_current', true)
         ->where('student_class_assignments.status', 'active');
    }

    /**
     * Get students in class (MANY TO MANY via enrollments).
     */
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'student_enrollments',
            'student_class_id',
            'student_id'
        );
    }

    /**
     * Get active students in this class via enrollments.
     */
    public function activeStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_enrollments',
            'student_class_id',
            'student_id'
        )->where('is_active', true);
    }

    /**
     * Get students enrolled in this class for the current academic year.
     */
    public function currentAcademicYearStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_enrollments',
            'student_class_id',
            'student_id'
        )->whereHas('enrollments', function ($query) {
            $query->whereHas('academicYear', function ($q) {
                $q->where('is_active', true);
            });
        });
    }

    /**
     * Get class subjects (MANY TO MANY).
     */
    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'student_class_subject',
            'student_class_id',
            'subject_id'
        );
    }

    /**
     * Get subject staff assignments.
     */
    public function subjectStaff()
    {
        return $this->hasMany(ClassSubjectStaff::class);
    }

    /**
     * Get attendance sessions for this class.
     */
    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    /**
     * Get staff linked to class (via pivot table).
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'student_class_staff', 'student_class_id', 'staff_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the class teacher's full name.
     */
    public function getTeacherNameAttribute()
    {
        return $this->classTeacher ? $this->classTeacher->full_name : 'Not Assigned';
    }

    /**
     * Get the class name with stream.
     */
    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . ($this->stream ?? ''));
    }

    /**
     * Get the class display name with code.
     */
    public function getDisplayNameAttribute()
    {
        return trim($this->name . ' ' . ($this->stream ?? '') . ' (' . $this->student_class_code . ')');
    }

    /**
     * Get the total number of students in the class via enrollments.
     */
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    /**
     * Get the total number of active students in the class via enrollments.
     */
    public function getActiveStudentCountAttribute()
    {
        return $this->activeStudents()->count();
    }

    /**
     * Get the total number of assigned students via student class assignments.
     */
    public function getAssignedStudentCountAttribute()
    {
        return $this->studentClassAssignments()
            ->where('is_current', true)
            ->where('status', 'active')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include classes with active assignments.
     */
    public function scopeWithActiveAssignments($query)
    {
        return $query->whereHas('studentClassAssignments', function($q) {
            $q->where('is_current', true)->where('status', 'active');
        });
    }

    /**
     * Scope a query to filter by class type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('class_type', $type);
    }

    /**
     * Scope a query to filter by education type.
     */
    public function scopeOfEducationType($query, $type)
    {
        return $query->where('education_type', $type);
    }

    /**
     * Scope a query to filter by academic year.
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT & AUTO GENERATE CLASS CODE
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($class) {
            if (!$class->student_class_code) {
                $year = AcademicYear::find($class->academic_year_id);
                $yearLabel = $year
                    ? date('Y', strtotime($year->start_date))
                    : date('Y');

                $prefix = 'CLS';

                if ($class->class_type == 'Kindergarten (KG)') {
                    $prefix = 'KG';
                } elseif ($class->class_type == 'JHS') {
                    $prefix = 'JHS';
                } elseif ($class->class_type == 'Lower Primary') {
                    $prefix = 'LP';
                } elseif ($class->class_type == 'Higher Primary') {
                    $prefix = 'HP';
                }

                $base = strtoupper(
                    $prefix . '-' .
                    Str::slug($class->stream ?? '') . '-' .
                    $yearLabel
                );

                $code = $base;
                $counter = 1;

                while (self::where('student_class_code', $code)->exists()) {
                    $code = $base . '-' . $counter;
                    $counter++;
                }

                $class->student_class_code = $code;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the class has students.
     */
    public function hasStudents()
    {
        return $this->students()->exists();
    }

    /**
     * Check if the class has active students.
     */
    public function hasActiveStudents()
    {
        return $this->activeStudents()->exists();
    }

    /**
     * Get the class size (number of students).
     */
    public function getClassSize()
    {
        return $this->students()->count();
    }

    /**
     * Get the percentage of capacity used.
     */
    public function getCapacityPercentage()
    {
        if (!$this->capacity || $this->capacity == 0) {
            return 0;
        }
        
        $count = $this->students()->count();
        return round(($count / $this->capacity) * 100, 2);
    }

    /**
     * Check if the class is full.
     */
    public function isFull()
    {
        if (!$this->capacity) {
            return false;
        }
        
        return $this->students()->count() >= $this->capacity;
    }

    /**
     * Get the available slots in the class.
     */
    public function getAvailableSlots()
    {
        if (!$this->capacity) {
            return 'Unlimited';
        }
        
        $available = $this->capacity - $this->students()->count();
        return max(0, $available);
    }

    public function assignments()
    {
        return $this->studentClassAssignments();
    }
}