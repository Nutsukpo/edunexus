<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassStaff extends Model
{
    use HasFactory;

    protected $table = 'student_class_staff';

    protected $fillable = [
        'student_class_id',
        'staff_id',
        'academic_year_id',
        'term_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * The student class assigned to the Form Master.
     */
    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    /**
     * The staff member assigned as Form Master.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * The academic year for this assignment.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * The term for this assignment.
     */
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Limit assignments to a specific academic period.
     */
    public function scopeForPeriod($query, int $academicYearId, int $termId)
    {
        return $query
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId);
    }

    /**
     * Limit assignments to a specific staff member.
     */
    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Limit assignments to a specific class.
     */
    public function scopeForClass($query, int $classId)
    {
        return $query->where('student_class_id', $classId);
    }
}