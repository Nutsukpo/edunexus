<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFeeAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_class_id',
        'academic_year_id',
        'student_class_assignment_id',
        'total_fees',
        'amount_paid',
        'balance',
        'discount_applied',
        'waiver_amount',
        'status',
        'is_active',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'total_fees' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'discount_applied' => 'decimal:2',
        'waiver_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
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

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentClassAssignment()
    {
        return $this->belongsTo(StudentClassAssignment::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function feeItems()
    {
        return $this->hasMany(StudentFeeItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate total fees for a student based on class fee structure
     */
    public static function calculateStudentFees($studentId, $academicYearId, $classId = null)
    {
        // Get the student's current assignment
        $assignment = StudentClassAssignment::where('student_id', $studentId)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return 0;
        }

        $classId = $classId ?? $assignment->student_class_id;

        // Get all active fee structures for this class and academic year
        $feeStructures = ClassFeeStructure::where('student_class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->get();

        return $feeStructures->sum('amount');
    }

    /**
     * Get detailed fee breakdown for a student
     */
    public static function getStudentFeeBreakdown($studentId, $academicYearId)
    {
        $assignment = StudentClassAssignment::where('student_id', $studentId)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return [
                'total_fees' => 0,
                'fee_items' => [],
                'class_name' => null,
                'academic_year' => null,
            ];
        }

        $feeStructures = ClassFeeStructure::where('student_class_id', $assignment->student_class_id)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->get();

        $totalFees = $feeStructures->sum('amount');

        return [
            'total_fees' => $totalFees,
            'fee_items' => $feeStructures->map(function($fee) {
                return [
                    'id' => $fee->id,
                    'fee_type' => $fee->fee_type,
                    'fee_name' => $fee->fee_name,
                    'amount' => $fee->amount,
                    'formatted_amount' => $fee->formatted_amount,
                    'is_required' => $fee->is_required,
                    'description' => $fee->description,
                    'due_date' => $fee->due_date,
                ];
            }),
            'class_name' => $assignment->studentClass->name ?? null,
            'academic_year' => $assignment->academicYear->name ?? null,
            'assignment_id' => $assignment->id,
        ];
    }

            /**
     * Calculate the status based on total fees and amount paid.
     */
    public function calculateStatus(): string
    {
        $totalFees = (float) ($this->total_fees ?? 0);
        $amountPaid = (float) ($this->amount_paid ?? 0);
        $balance = (float) ($this->balance ?? 0);
        
        if ($totalFees <= 0) {
            return 'pending';
        }
        
        if ($balance <= 0 && $amountPaid > 0) {
            return 'paid';
        }
        
        if ($amountPaid > 0) {
            return 'partial';
        }
        
        return 'pending';
    }
}