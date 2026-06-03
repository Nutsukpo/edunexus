<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [

        'student_id',

        'first_name',
        'middle_name',
        'last_name',

        'gender',
        'date_of_birth',

        'nationality',
        'religion',

        // ADDRESS
        'address',

        // DISABILITY
        'has_disability',
        'disability_type',

        // FATHER
        'father_name',
        'father_phone',
        'father_email',
        'father_occupation',

        // MOTHER
        'mother_name',
        'mother_phone',
        'mother_email',
        'mother_occupation',

        // GUARDIAN
        'guardian_name',
        'guardian_phone',
        'guardian_email',

        // SCHOOL
        'admission_date',
        'photo',
        'is_active',
    ];

    protected $casts = [

        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'has_disability' => 'boolean',
        'is_active' => 'boolean',
    
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {

            $last = self::latest()->first();

            $number = 1;

            if ($last && $last->student_id) {
                $number = (int) str_replace('STD-', '', $last->student_id) + 1;
            }

            $student->student_id = 'STD-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

        public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function fees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function classAssignments()
    {
        return $this->hasMany(StudentClassAssignment::class);
    }

    public function currentClassAssignment()
    {
        return $this->hasOne(StudentClassAssignment::class)
            ->where('is_current', true);
    }

            // In app/Models/Student.php



    public function currentAssignment()
    {
        return $this->hasOne(StudentClassAssignment::class, 'student_id')
            ->where('is_current', true);
    }


}