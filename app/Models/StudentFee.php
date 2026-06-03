<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    protected $fillable = [

        'student_id',
        'academic_year_id',
        'term_id',
        'student_class_id',
        'fee_structure_id',

        'total_fee',
        'amount_paid',
        'balance',

        'payment_status',

        'due_date',
        'remarks',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

        
}