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
    | ACADEMIC RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Academic year this class belongs to.
     */
    public function academicYear()
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM MASTER / CLASS STAFF
    |--------------------------------------------------------------------------
    */

    /**
     * Legacy/direct Form Master stored on student_classes.staff_id.
     *
     * This is preserved for compatibility with the existing system.
     */
    public function classTeacher()
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    /**
     * Form Master assignment records.
     *
     * Uses the period-aware student_class_staff table.
     */
    public function formMasterAssignments()
    {
        return $this->hasMany(
            StudentClassStaff::class,
            'student_class_id'
        );
    }

    /**
     * Staff assigned to this class through student_class_staff.
     *
     * These are Form Master/class staff assignments.
     */
    public function staff()
    {
        return $this->belongsToMany(
            Staff::class,
            'student_class_staff',
            'student_class_id',
            'staff_id'
        )
        ->withPivot([
            'academic_year_id',
            'term_id',
        ])
        ->withTimestamps();
    }

    /**
     * Form Masters assigned to this class.
     *
     * Alias for clarity when working with the new authorization system.
     */
    public function formMasters()
    {
        return $this->belongsToMany(
            Staff::class,
            'student_class_staff',
            'student_class_id',
            'staff_id'
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
    */

    /**
     * Subject Teacher assignment records.
     *
     * Each record connects:
     * Class + Subject + Staff + Academic Year + Term.
     */
    public function subjectStaff()
    {
        return $this->hasMany(
            ClassSubjectStaff::class,
            'student_class_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS PREFECT
    |--------------------------------------------------------------------------
    */

    public function classPrefect()
    {
        return $this->belongsTo(
            Student::class,
            'class_prefect_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STUDENT ASSIGNMENTS
    |--------------------------------------------------------------------------
    */

    /**
     * Legacy enrollments relationship.
     */
    public function enrollments()
    {
        return $this->hasMany(
            StudentEnrollment::class
        );
    }

    /**
     * Student class assignments.
     */
    public function studentClassAssignments()
    {
        return $this->hasMany(
            StudentClassAssignment::class,
            'student_class_id'
        );
    }

    /**
     * Current active student assignments.
     */
    public function activeStudentClassAssignments()
    {
        return $this->hasMany(
            StudentClassAssignment::class,
            'student_class_id'
        )
        ->where('is_current', true)
        ->where('status', 'active');
    }

    /**
     * Students assigned to this class through
     * student_class_assignments.
     */
    public function assignedStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            StudentClassAssignment::class,
            'student_class_id',
            'id',
            'id',
            'student_id'
        )
        ->where(
            'student_class_assignments.is_current',
            true
        )
        ->where(
            'student_class_assignments.status',
            'active'
        );
    }

    /**
     * Legacy students relationship.
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
     * Active students through legacy enrollments.
     */
    public function activeStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_enrollments',
            'student_class_id',
            'student_id'
        )
        ->where('is_active', true);
    }

    /**
     * Students enrolled during the active academic year.
     */
    public function currentAcademicYearStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_enrollments',
            'student_class_id',
            'student_id'
        )
        ->whereHas('enrollments', function ($query) {
            $query->whereHas(
                'academicYear',
                function ($q) {
                    $q->where('is_active', true);
                }
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECTS
    |--------------------------------------------------------------------------
    */

    /**
     * Subjects attached to this class.
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

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */

    public function attendanceSessions()
    {
        return $this->hasMany(
            AttendanceSession::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Legacy class teacher display name.
     */
    public function getTeacherNameAttribute()
    {
        return $this->classTeacher
            ? $this->classTeacher->full_name
            : 'Not Assigned';
    }

    /**
     * Class name with stream.
     */
    public function getFullNameAttribute()
    {
        return trim(
            $this->name . ' ' . ($this->stream ?? '')
        );
    }

    /**
     * Class display name with code.
     */
    public function getDisplayNameAttribute()
    {
        return trim(
            $this->name .
            ' ' .
            ($this->stream ?? '') .
            ' (' .
            $this->student_class_code .
            ')'
        );
    }

    /**
     * Student count through legacy enrollments.
     */
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    /**
     * Active student count through legacy enrollments.
     */
    public function getActiveStudentCountAttribute()
    {
        return $this->activeStudents()->count();
    }

    /**
     * Current active assignment count.
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithActiveAssignments($query)
    {
        return $query->whereHas(
            'studentClassAssignments',
            function ($q) {
                $q->where('is_current', true)
                    ->where('status', 'active');
            }
        );
    }

    public function scopeOfType($query, $type)
    {
        return $query->where(
            'class_type',
            $type
        );
    }

    public function scopeOfEducationType($query, $type)
    {
        return $query->where(
            'education_type',
            $type
        );
    }

    public function scopeForAcademicYear(
        $query,
        $academicYearId
    ) {
        return $query->where(
            'academic_year_id',
            $academicYearId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLASS CODE GENERATION
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($class) {

            if (!$class->student_class_code) {

                $year = AcademicYear::find(
                    $class->academic_year_id
                );

                $yearLabel = $year
                    ? date(
                        'Y',
                        strtotime($year->start_date)
                    )
                    : date('Y');

                $prefix = 'CLS';

                if (
                    $class->class_type ===
                    'Kindergarten (KG)'
                ) {
                    $prefix = 'KG';

                } elseif (
                    $class->class_type === 'JHS'
                ) {
                    $prefix = 'JHS';

                } elseif (
                    $class->class_type ===
                    'Lower Primary'
                ) {
                    $prefix = 'LP';

                } elseif (
                    $class->class_type ===
                    'Higher Primary'
                ) {
                    $prefix = 'HP';
                }

                $base = strtoupper(
                    $prefix .
                    '-' .
                    Str::slug(
                        $class->stream ?? ''
                    ) .
                    '-' .
                    $yearLabel
                );

                $code = $base;
                $counter = 1;

                while (
                    self::where(
                        'student_class_code',
                        $code
                    )->exists()
                ) {
                    $code =
                        $base .
                        '-' .
                        $counter;

                    $counter++;
                }

                $class->student_class_code =
                    $code;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    public function hasStudents()
    {
        return $this->students()->exists();
    }

    public function hasActiveStudents()
    {
        return $this->activeStudents()->exists();
    }

    public function getClassSize()
    {
        return $this->students()->count();
    }

    public function getCapacityPercentage()
    {
        if (
            !$this->capacity ||
            $this->capacity == 0
        ) {
            return 0;
        }

        $count = $this->students()->count();

        return round(
            ($count / $this->capacity) * 100,
            2
        );
    }

    public function isFull()
    {
        if (!$this->capacity) {
            return false;
        }

        return $this->students()->count()
            >= $this->capacity;
    }

    public function getAvailableSlots()
    {
        if (!$this->capacity) {
            return 'Unlimited';
        }

        $available =
            $this->capacity -
            $this->students()->count();

        return max(0, $available);
    }

    /**
     * Alias for student class assignments.
     */
    public function assignments()
    {
        return $this->studentClassAssignments();
    }
}