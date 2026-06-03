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

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /**
     * Class Subjects (MANY TO MANY)
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



    public function subjectStaff()
    {
        return $this->hasMany(ClassSubjectStaff::class);
    }

    public function classPrefect()
    {
        return $this->belongsTo(Student::class, 'class_prefect_id');
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function classAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class);
    }

    public function assignments()
    {
        return $this->hasMany(StudentClassAssignment::class, 'student_class_id');
    }

        // In app/Models/StudentClass.php


    /**
     * Students in class (BETTER via enrollments usually, but kept as-is)
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
     * Staff linked to class (ONLY if you truly have pivot table)
     * If NOT using pivot table, REMOVE this and rely on classTeacher()
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'student_class_staff', 'student_class_id', 'staff_id');
    }


        

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->stream;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE CLASS CODE
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
                }

                if ($class->class_type == 'JHS') {
                    $prefix = 'JHS';
                }

                if ($class->class_type == 'Lower Primary') {
                    $prefix = 'LP';
                }

                if ($class->class_type == 'Higher Primary') {
                    $prefix = 'HP';
                }

                $base = strtoupper(
                    $prefix . '-' .
                    Str::slug($class->stream) . '-' .
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
}