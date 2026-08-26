<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FeePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_class_assignment_id',
        'student_fee_account_id',
        'bill_sheet_id',
        'bill_sheet_item_id',
        'fee_item_id',

        'receipt_number',
        'transaction_id',

        'amount',
        'penalty_amount',
        'discount_amount',
        'net_amount',

        'payment_method',
        'bank_name',
        'cheque_number',
        'reference_number',

        'payment_date',
        'notes',
        'status',
        'payment_type',
        'recorded_by',
        'metadata',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'penalty_amount'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount'      => 'decimal:2',

        'payment_date' => 'date',

        'metadata' => 'array',
    ];


    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($payment) {

            /*
            |--------------------------------------------------------------------------
            | AUTO-GENERATE RECEIPT NUMBER
            |--------------------------------------------------------------------------
            */

            if (empty($payment->receipt_number)) {

                $nextId = ((int) static::withTrashed()->max('id')) + 1;

                $payment->receipt_number =
                    'RCP-'
                    . now()->format('Y')
                    . '-'
                    . str_pad(
                        $nextId,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | AUTO-GENERATE TRANSACTION ID
            |--------------------------------------------------------------------------
            |
            | If the cashier does not enter a transaction ID, EDUNEXUS
            | automatically generates one.
            |
            | Example:
            |
            | TXN-20260812-A7K92F
            |
            */

            if (empty($payment->transaction_id)) {

                $payment->transaction_id =
                    static::generateTransactionId();
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE UNIQUE TRANSACTION ID
    |--------------------------------------------------------------------------
    */

    public static function generateTransactionId(): string
    {
        do {

            $transactionId =
                'TID-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(
                    Str::random(5)
                );

        } while (
            static::withTrashed()
                ->where(
                    'transaction_id',
                    $transactionId
                )
                ->exists()
        );

        return $transactionId;
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(
            Student::class
        );
    }


    public function studentClassAssignment()
    {
        return $this->belongsTo(
            StudentClassAssignment::class,
            'student_class_assignment_id'
        );
    }


    public function studentFeeAccount()
    {
        return $this->belongsTo(
            StudentFeeAccount::class
        );
    }


    public function billSheet()
    {
        return $this->belongsTo(
            BillSheet::class
        );
    }


    public function billSheetItem()
    {
        return $this->belongsTo(
            BillSheetItem::class
        );
    }


    public function paymentItems()
    {
        return $this->hasMany(
            PaymentItem::class
        );
    }


    public function receipt()
    {
        return $this->hasOne(
            FeeReceipt::class
        );
    }


    public function feeItem()
    {
        return $this->belongsTo(
            StudentFeeItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STUDENT CLASS
    |--------------------------------------------------------------------------
    */

    public function studentClass()
    {
        return $this->hasOneThrough(
            StudentClass::class,
            StudentClassAssignment::class,
            'id',
            'id',
            'student_class_assignment_id',
            'student_class_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getStudentClassNameAttribute()
    {
        return $this->studentClassAssignment
            ?->studentClass
            ?->name
            ?? 'Not Assigned';
    }


    public function getAcademicYearNameAttribute()
    {
        return $this->studentClassAssignment
            ?->academicYear
            ?->name
            ?? 'N/A';
    }


    public function getFormattedAmountAttribute()
    {
        return 'GHS '
            . number_format(
                (float) $this->amount,
                2
            );
    }


    public function getFormattedNetAmountAttribute()
    {
        return 'GHS '
            . number_format(
                (float) $this->net_amount,
                2
            );
    }


    public function getFormattedTransactionIdAttribute()
    {
        return $this->transaction_id
            ?? 'N/A';
    }


    public function getStatusBadgeClassAttribute()
    {
        return [
            'pending'   => 'warning',
            'completed' => 'success',
            'failed'    => 'danger',
            'refunded'  => 'info',
            'reversed'  => 'secondary',
        ][$this->status] ?? 'secondary';
    }


    public function getStatusLabelAttribute()
    {
        return ucfirst(
            $this->status ?? 'Unknown'
        );
    }


    public function getPaymentMethodLabelAttribute()
    {
        return [
            'cash' =>
                'Cash',

            'bank_transfer' =>
                'Bank Transfer',

            'mobile_money' =>
                'Mobile Money',

            'card' =>
                'Card Payment',

            'cheque' =>
                'Cheque',

            'online' =>
                'Online Payment',

            'other' =>
                'Other',

        ][$this->payment_method]
            ?? $this->payment_method;
    }


    public function getPaymentTypeLabelAttribute()
    {
        return [
            'full' =>
                'Full Payment',

            'partial' =>
                'Partial Payment',

            'installment' =>
                'Installment',

            'advance' =>
                'Advance Payment',

        ][$this->payment_type]
            ?? $this->payment_type;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function isCompleted()
    {
        return $this->status === 'completed';
    }


    public function isPending()
    {
        return $this->status === 'pending';
    }


    public function isRefunded()
    {
        return $this->status === 'refunded';
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeCompleted($query)
    {
        return $query->where(
            'status',
            'completed'
        );
    }


    public function scopePending($query)
    {
        return $query->where(
            'status',
            'pending'
        );
    }


    public function scopeForStudent(
        $query,
        $studentId
    ) {
        return $query->where(
            'student_id',
            $studentId
        );
    }


    public function scopeForClass(
        $query,
        $classId
    ) {
        return $query->whereHas(
            'studentClassAssignment',
            function ($q) use ($classId) {

                $q->where(
                    'student_class_id',
                    $classId
                );
            }
        );
    }


    public function scopeForAcademicYear(
        $query,
        $academicYearId
    ) {
        return $query->whereHas(
            'studentClassAssignment',
            function ($q) use ($academicYearId) {

                $q->where(
                    'academic_year_id',
                    $academicYearId
                );
            }
        );
    }


    public function scopeDateRange(
        $query,
        $from,
        $to
    ) {

        if ($from) {

            $query->whereDate(
                'payment_date',
                '>=',
                $from
            );
        }


        if ($to) {

            $query->whereDate(
                'payment_date',
                '<=',
                $to
            );
        }


        return $query;
    }
}