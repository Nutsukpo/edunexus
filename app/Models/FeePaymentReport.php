<?php
// app/Models/FeePaymentReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeePaymentReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_fee_account_id',
        'academic_year_id',
        'term_id',
        'report_type',
        'total_fees',
        'total_paid',
        'total_balance',
        'total_discount',
        'total_waiver',
        'payment_count',
        'payment_status',
        'report_date',
        'generated_by',
        'metadata',
    ];

    protected $casts = [
        'total_fees' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_balance' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_waiver' => 'decimal:2',
        'payment_count' => 'integer',
        'report_date' => 'datetime',
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

    public function studentFeeAccount()
    {
        return $this->belongsTo(StudentFeeAccount::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    /*
    |--------------------------------------------------------------------------
    | METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a comprehensive fee payment report
     */
    public static function generateReport($filters = [])
    {
        $query = StudentFeeAccount::with(['student', 'academicYear', 'studentClass']);

        // Apply filters
        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['student_class_id'])) {
            $query->where('student_class_id', $filters['student_class_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        }

        $feeAccounts = $query->get();

        $reportData = [];
        $summary = [
            'total_fees' => 0,
            'total_paid' => 0,
            'total_balance' => 0,
            'total_discount' => 0,
            'total_waiver' => 0,
            'total_students' => $feeAccounts->count(),
            'fully_paid' => 0,
            'partial_paid' => 0,
            'pending' => 0,
        ];

        foreach ($feeAccounts as $account) {
            // Calculate payment details
            $payments = $account->payments()->where('status', 'completed')->get();
            $paymentCount = $payments->count();
            $totalPaid = $payments->sum('amount');

            // Get fee items breakdown
            $feeItems = $account->feeItems;
            $feeBreakdown = [];
            foreach ($feeItems as $item) {
                $feeBreakdown[] = [
                    'fee_name' => $item->fee_name,
                    'amount' => $item->amount,
                    'paid' => $item->paid_amount ?? 0,
                    'balance' => $item->balance ?? $item->amount,
                ];
            }

            $status = $account->calculateStatus();

            // Update summary
            $summary['total_fees'] += $account->total_fees;
            $summary['total_paid'] += $totalPaid;
            $summary['total_balance'] += $account->balance;
            $summary['total_discount'] += $account->discount_applied ?? 0;
            $summary['total_waiver'] += $account->waiver_amount ?? 0;

            if ($status === 'paid') {
                $summary['fully_paid']++;
            } elseif ($status === 'partial') {
                $summary['partial_paid']++;
            } else {
                $summary['pending']++;
            }

            $reportData[] = [
                'student' => $account->student,
                'account' => $account,
                'total_fees' => $account->total_fees,
                'total_paid' => $totalPaid,
                'balance' => $account->balance,
                'discount_applied' => $account->discount_applied,
                'waiver_amount' => $account->waiver_amount,
                'status' => $status,
                'payment_count' => $paymentCount,
                'fee_breakdown' => $feeBreakdown,
                'payments' => $payments,
                'class_name' => $account->studentClass->name ?? 'N/A',
                'academic_year' => $account->academicYear->name ?? 'N/A',
            ];
        }

        return [
            'report_data' => $reportData,
            'summary' => $summary,
            'generated_at' => now(),
            'filters' => $filters,
        ];
    }

    /**
     * Get fee collection statistics
     */
    public static function getFeeCollectionStats($academicYearId = null, $termId = null)
    {
        $query = StudentFeeAccount::query();

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($termId) {
            $query->whereHas('feeItems', function ($q) use ($termId) {
                $q->where('term_id', $termId);
            });
        }

        $totalAccounts = $query->count();
        $totalFees = $query->sum('total_fees');
        $totalPaid = $query->sum('amount_paid');
        $totalBalance = $query->sum('balance');
        $totalDiscount = $query->sum('discount_applied');
        $totalWaiver = $query->sum('waiver_amount');

        // Collection rate
        $collectionRate = $totalFees > 0 
            ? round(($totalPaid / $totalFees) * 100, 2) 
            : 0;

        // Status distribution
        $statusDistribution = [
            'paid' => $query->clone()->where('status', 'paid')->count(),
            'partial' => $query->clone()->where('status', 'partial')->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
        ];

        return [
            'total_accounts' => $totalAccounts,
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'total_discount' => $totalDiscount,
            'total_waiver' => $totalWaiver,
            'collection_rate' => $collectionRate,
            'status_distribution' => $statusDistribution,
        ];
    }
}