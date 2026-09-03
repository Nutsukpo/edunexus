<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FeePayment extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'fee_payments';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'amount'          => 'decimal:2',
        'penalty_amount'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount'      => 'decimal:2',

        'payment_date'    => 'date',

        'metadata'        => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
{
    static::creating(function (FeePayment $payment): void {

        /*
        |--------------------------------------------------------------------------
        | Generate receipt number
        |--------------------------------------------------------------------------
        */

        if (empty($payment->receipt_number)) {
            $nextId = ((int) static::withTrashed()->max('id')) + 1;

            $payment->receipt_number =
                'RCP-' .
                now()->format('Y') .
                '-' .
                str_pad($nextId, 6, '0', STR_PAD_LEFT);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate transaction ID
        |--------------------------------------------------------------------------
        */

        if (empty($payment->transaction_id)) {
            $payment->transaction_id =
                static::generateTransactionId();
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate net amount
        |--------------------------------------------------------------------------
        */

        if (
            $payment->net_amount === null &&
            $payment->amount !== null
        ) {
            $amount = (float) $payment->amount;
            $penalty = (float) ($payment->penalty_amount ?? 0);
            $discount = (float) ($payment->discount_amount ?? 0);

            $payment->net_amount = max(
                0,
                $amount + $penalty - $discount
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Defaults
        |--------------------------------------------------------------------------
        */

        if (empty($payment->payment_date)) {
            $payment->payment_date = now()->toDateString();
        }

        if (empty($payment->status)) {
            $payment->status = 'pending';
        }

        if (empty($payment->payment_type)) {
            $payment->payment_type = 'partial';
        }
    });


    /*
    |--------------------------------------------------------------------------
    | PAYMENT STATUS CHANGED
    |--------------------------------------------------------------------------
    |
    | When a payment changes to completed:
    |
    | 1. Recalculate the student's fee account
    | 2. Update amount paid
    | 3. Update balance
    | 4. Update account status
    | 5. Create a receipt record
    |
    */

    static::updated(function (FeePayment $payment): void {

        if (
            !$payment->wasChanged('status') ||
            $payment->status !== 'completed'
        ) {
            return;
        }

        DB::transaction(function () use ($payment): void {

            /*
            |--------------------------------------------------------------------------
            | Update fee account
            |--------------------------------------------------------------------------
            */

            if ($payment->student_fee_account_id) {

                $account = StudentFeeAccount::find(
                    $payment->student_fee_account_id
                );

                if ($account) {

                    $totalPaid = (float) static::query()
                        ->where(
                            'student_fee_account_id',
                            $account->id
                        )
                        ->where(
                            'status',
                            'completed'
                        )
                        ->sum('net_amount');

                    $totalFees = (float) (
                        $account->total_fees ?? 0
                    );

                    $balance = max(
                        0,
                        round(
                            $totalFees - $totalPaid,
                            2
                        )
                    );

                    $account->amount_paid = round(
                        $totalPaid,
                        2
                    );

                    $account->balance = $balance;

                    $account->status =
                        $balance <= 0
                            ? 'paid'
                            : (
                                $totalPaid > 0
                                    ? 'partial'
                                    : 'pending'
                            );

                    $account->save();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create receipt
            |--------------------------------------------------------------------------
            */

            if (
                class_exists(\App\Models\FeeReceipt::class) &&
                !\App\Models\FeeReceipt::where(
                    'fee_payment_id',
                    $payment->id
                )->exists()
            ) {

                \App\Models\FeeReceipt::create([

                    'fee_payment_id' =>
                        $payment->id,

                    'receipt_number' =>
                        $payment->receipt_number,

                    'receipt_template' =>
                        'student-payment',

                    'receipt_data' => [
                        'payment_id' =>
                            $payment->id,

                        'receipt_number' =>
                            $payment->receipt_number,

                        'student_id' =>
                            $payment->student_id,

                        'student_fee_account_id' =>
                            $payment->student_fee_account_id,

                        'amount' =>
                            (float) $payment->amount,

                        'net_amount' =>
                            (float) $payment->net_amount,

                        'payment_method' =>
                            $payment->payment_method,

                        'payment_date' =>
                            optional(
                                $payment->payment_date
                            )->format('Y-m-d'),

                        'reference_number' =>
                            $payment->reference_number,

                        'transaction_id' =>
                            $payment->transaction_id,

                        'generated_at' =>
                            now()->toDateTimeString(),
                    ],

                    'generated_at' =>
                        now(),
                ]);
            }
        });
    });
}

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION ID GENERATOR
    |--------------------------------------------------------------------------
    */

    public static function generateTransactionId(): string
    {
        do {
            $transactionId =
                'TID-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(5));

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

    /**
     * Student who made the payment.
     */
    public function student()
    {
        return $this->belongsTo(
            Student::class,
            'student_id'
        );
    }

    /**
     * Student's class assignment at the time of payment.
     */
    public function studentClassAssignment()
    {
        return $this->belongsTo(
            StudentClassAssignment::class,
            'student_class_assignment_id'
        );
    }

    /**
     * Student fee account receiving the payment.
     */
    public function studentFeeAccount()
    {
        return $this->belongsTo(
            StudentFeeAccount::class,
            'student_fee_account_id'
        );
    }

    /**
     * Bill sheet associated with the payment.
     */
    public function billSheet()
    {
        return $this->belongsTo(
            BillSheet::class,
            'bill_sheet_id'
        );
    }

    /**
     * Bill sheet item associated with the payment.
     */
    public function billSheetItem()
    {
        return $this->belongsTo(
            BillSheetItem::class,
            'bill_sheet_item_id'
        );
    }

    /**
     * Fee item associated with the payment.
     *
     * fee_item_id currently points to student_fee_items.
     */
    public function feeItem()
    {
        return $this->belongsTo(
            StudentFeeItem::class,
            'fee_item_id'
        );
    }

    /**
     * Payment item records.
     */
    public function paymentItems()
    {
        return $this->hasMany(
            PaymentItem::class,
            'fee_payment_id'
        );
    }

    /**
     * Receipt associated with the payment.
     *
     * This relationship is intentionally available only if the
     * fee_receipts table/model exists in the project.
     */
    public function receipt()
    {
        return $this->hasOne(
            FeeReceipt::class,
            'fee_payment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Student's class name.
     */
    public function getStudentClassNameAttribute(): string
    {
        return $this->studentClassAssignment
            ?->studentClass
            ?->name
            ?? 'Not Assigned';
    }

    /**
     * Academic year name.
     */
    public function getAcademicYearNameAttribute(): string
    {
        return $this->studentClassAssignment
            ?->academicYear
            ?->name
            ?? 'N/A';
    }

    /**
     * Formatted gross amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'GHS ' . number_format(
            (float) ($this->amount ?? 0),
            2
        );
    }

    /**
     * Formatted penalty.
     */
    public function getFormattedPenaltyAmountAttribute(): string
    {
        return 'GHS ' . number_format(
            (float) ($this->penalty_amount ?? 0),
            2
        );
    }

    /**
     * Formatted discount.
     */
    public function getFormattedDiscountAmountAttribute(): string
    {
        return 'GHS ' . number_format(
            (float) ($this->discount_amount ?? 0),
            2
        );
    }

    /**
     * Formatted net amount.
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return 'GHS ' . number_format(
            (float) ($this->net_amount ?? 0),
            2
        );
    }

    /**
     * Formatted payment date.
     */
    public function getFormattedPaymentDateAttribute(): string
    {
        return $this->payment_date
            ? $this->payment_date->format('M d, Y')
            : 'N/A';
    }

    /**
     * Transaction ID.
     */
    public function getFormattedTransactionIdAttribute(): string
    {
        return $this->transaction_id ?? 'N/A';
    }

    /**
     * Receipt number.
     */
    public function getFormattedReceiptNumberAttribute(): string
    {
        return $this->receipt_number ?? 'N/A';
    }

    /**
     * Bootstrap badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'pending'   => 'warning',
            'failed'    => 'danger',
            'refunded'  => 'info',
            'reversed'  => 'secondary',
            default     => 'secondary',
        };
    }

    /**
     * Human-readable status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Completed',
            'pending'   => 'Pending',
            'failed'    => 'Failed',
            'refunded'  => 'Refunded',
            'reversed'  => 'Reversed',
            default     => ucfirst(
                (string) ($this->status ?? 'Unknown')
            ),
        };
    }

    /**
     * Human-readable payment method.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money'  => 'Mobile Money',
            'card'          => 'Card Payment',
            'cheque'        => 'Cheque',
            'online'        => 'Online Payment',
            'other'         => 'Other',
            default         => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $this->payment_method
                )
            ),
        };
    }

    /**
     * Human-readable payment type.
     */
    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'full'        => 'Full Payment',
            'partial'     => 'Partial Payment',
            'installment' => 'Installment',
            'advance'     => 'Advance Payment',
            default       => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $this->payment_type
                )
            ),
        };
    }

    /**
     * Whether this is a mobile money payment.
     */
    public function getIsMobileMoneyAttribute(): bool
    {
        return $this->payment_method === 'mobile_money';
    }

    /**
     * Whether the payment has been completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT CALCULATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the net amount from the payment components.
     */
    public function calculateNetAmount(): float
    {
        $amount = (float) ($this->amount ?? 0);
        $penalty = (float) ($this->penalty_amount ?? 0);
        $discount = (float) ($this->discount_amount ?? 0);

        return max(
            0,
            $amount + $penalty - $discount
        );
    }

    /**
     * Recalculate and save the net amount.
     */
    public function recalculateNetAmount(): bool
    {
        $this->net_amount = $this->calculateNetAmount();

        return $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where(
            'status',
            'completed'
        );
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            'pending'
        );
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where(
            'status',
            'failed'
        );
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where(
            'status',
            'refunded'
        );
    }

    public function scopeForStudent(
        Builder $query,
        int $studentId
    ): Builder {
        return $query->where(
            'student_id',
            $studentId
        );
    }

    public function scopeForAccount(
        Builder $query,
        int $accountId
    ): Builder {
        return $query->where(
            'student_fee_account_id',
            $accountId
        );
    }

    public function scopeForAssignment(
        Builder $query,
        int $assignmentId
    ): Builder {
        return $query->where(
            'student_class_assignment_id',
            $assignmentId
        );
    }

    public function scopeForClass(
        Builder $query,
        int $classId
    ): Builder {
        return $query->whereHas(
            'studentClassAssignment',
            function (Builder $q) use ($classId): void {
                $q->where(
                    'student_class_id',
                    $classId
                );
            }
        );
    }

    public function scopeForAcademicYear(
        Builder $query,
        int $academicYearId
    ): Builder {
        return $query->whereHas(
            'studentClassAssignment',
            function (Builder $q) use ($academicYearId): void {
                $q->where(
                    'academic_year_id',
                    $academicYearId
                );
            }
        );
    }

    public function scopeMobileMoney(
        Builder $query
    ): Builder {
        return $query->where(
            'payment_method',
            'mobile_money'
        );
    }

    public function scopeDateRange(
        Builder $query,
        $from = null,
        $to = null
    ): Builder {
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