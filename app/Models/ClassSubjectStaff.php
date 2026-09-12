<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubjectStaff extends Model
{
    use HasFactory;

    protected $table = 'class_subject_staff';

    protected $fillable = [
        'student_class_id',
        'subject_id',
        'staff_id',
        'academic_year_id',
        'term_id',
        'day',
        'start_time',
        'end_time',
        'room',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Class assigned to this Subject Teacher.
     */
    public function studentClass()
    {
        return $this->belongsTo(
            StudentClass::class,
            'student_class_id'
        );
    }

    /**
     * Subject being taught.
     */
    public function subject()
    {
        return $this->belongsTo(
            Subject::class,
            'subject_id'
        );
    }

    /**
     * Staff member assigned to teach the subject.
     */
    public function staff()
    {
        return $this->belongsTo(
            Staff::class,
            'staff_id'
        );
    }

    /**
     * Academic year of the assignment.
     */
    public function academicYear()
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }

    /**
     * Term of the assignment.
     */
    public function term()
    {
        return $this->belongsTo(
            Term::class,
            'term_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Filter assignments by academic year and term.
     */
    public function scopeForPeriod(
        $query,
        int $academicYearId,
        int $termId
    ) {
        return $query
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'term_id',
                $termId
            );
    }

    /**
     * Filter assignments by staff.
     */
    public function scopeForStaff(
        $query,
        int $staffId
    ) {
        return $query->where(
            'staff_id',
            $staffId
        );
    }

    /**
     * Filter assignments by class.
     */
    public function scopeForClass(
        $query,
        int $classId
    ) {
        return $query->where(
            'student_class_id',
            $classId
        );
    }

    /**
     * Filter assignments by subject.
     */
    public function scopeForSubject(
        $query,
        int $subjectId
    ) {
        return $query->where(
            'subject_id',
            $subjectId
        );
    }
}