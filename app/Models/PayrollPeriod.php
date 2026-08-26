<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class PayrollPeriod extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'period_code',
        'name',
        'academic_year_id',
        'month',
        'year',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'description',
        'created_by',
        'approved_by',
        'approved_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'month' => 'integer',
        'year' => 'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Months
    |--------------------------------------------------------------------------
    */

    public static function getMonths(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }


    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'created_by'
        );
    }


    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            Staff::class,
            'approved_by'
        );
    }


    /**
     * Payroll period staff relationship.
     *
     * IMPORTANT:
     * The pivot table structure may differ between installations.
     * Therefore, only request columns that actually exist.
     */
    public function staff(): BelongsToMany
    {
        $pivotTable = 'payroll_period_staff';

        $baseColumns = [
            'payroll_period_id',
            'staff_id',
            'created_at',
            'updated_at',
        ];

        $optionalColumns = [
            'basic_salary',
            'allowances',
            'overtime',
            'gross_pay',
            'tax',
            'pension',
            'deductions',
            'net_pay',
            'worked_days',
            'hours_worked',
        ];

        try {

            $existingColumns = Schema::getColumnListing(
                $pivotTable
            );

            $pivotColumns = array_merge(
                $baseColumns,
                array_values(
                    array_intersect(
                        $optionalColumns,
                        $existingColumns
                    )
                )
            );

        } catch (\Throwable $e) {

            $pivotColumns = $baseColumns;
        }


        return $this->belongsToMany(
            Staff::class,
            $pivotTable,
            'payroll_period_id',
            'staff_id'
        )
        ->withTimestamps()
        ->withPivot(
            array_values(
                array_unique($pivotColumns)
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Payroll Items
    |--------------------------------------------------------------------------
    */

    public function payrollItems(): HasMany
    {
        return $this->hasMany(
            PayrollItem::class,
            'payroll_period_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft($query)
    {
        return $query->where(
            'status',
            self::STATUS_DRAFT
        );
    }


    public function scopeProcessing($query)
    {
        return $query->where(
            'status',
            self::STATUS_PROCESSING
        );
    }


    public function scopePendingApproval($query)
    {
        return $query->where(
            'status',
            self::STATUS_PENDING_APPROVAL
        );
    }


    public function scopeApproved($query)
    {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }


    public function scopeRejected($query)
    {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }


    public function scopePaid($query)
    {
        return $query->where(
            'status',
            self::STATUS_PAID
        );
    }


    public function scopeCancelled($query)
    {
        return $query->where(
            'status',
            self::STATUS_CANCELLED
        );
    }


    public function scopeActive($query)
    {
        return $query->whereNotIn(
            'status',
            [
                self::STATUS_PAID,
                self::STATUS_CANCELLED,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }


    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }


    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }


    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }


    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }


    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }


    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }


    /*
    |--------------------------------------------------------------------------
    | Workflow Helpers
    |--------------------------------------------------------------------------
    */

    public function canBeSubmittedForApproval(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_PROCESSING,
                self::STATUS_REJECTED,
            ],
            true
        );
    }


    public function canBeApproved(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PENDING_APPROVAL,
                self::STATUS_PROCESSING,
            ],
            true
        );
    }


    public function canBeRejected(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PENDING_APPROVAL,
                self::STATUS_PROCESSING,
            ],
            true
        );
    }


    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }


    public function isEditable(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_PROCESSING,
                self::STATUS_REJECTED,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Pivot Value
    |--------------------------------------------------------------------------
    */

    protected function pivotValue(
        $staff,
        string $column,
        $default = 0
    ) {
        if (!$staff || !$staff->pivot) {
            return $default;
        }

        return $staff->pivot->{$column} ?? $default;
    }


    /*
    |--------------------------------------------------------------------------
    | Payroll Totals
    |--------------------------------------------------------------------------
    */

    public function getTotalPayrollAttribute(): float
    {
        return (float) $this->staff->sum(
            function ($staff) {

                return $this->pivotValue(
                    $staff,
                    'net_pay',
                    0
                );
            }
        );
    }


    public function getTotalGrossAttribute(): float
    {
        return (float) $this->staff->sum(
            function ($staff) {

                return $this->pivotValue(
                    $staff,
                    'gross_pay',
                    0
                );
            }
        );
    }


    public function getTotalDeductionsAttribute(): float
    {
        return (float) $this->staff->sum(
            function ($staff) {

                return $this->pivotValue(
                    $staff,
                    'deductions',
                    0
                );
            }
        );
    }


    public function getTotalTaxAttribute(): float
    {
        return (float) $this->staff->sum(
            function ($staff) {

                return $this->pivotValue(
                    $staff,
                    'tax',
                    0
                );
            }
        );
    }


    public function getTotalPensionAttribute(): float
    {
        return (float) $this->staff->sum(
            function ($staff) {

                return $this->pivotValue(
                    $staff,
                    'pension',
                    0
                );
            }
        );
    }


    public function getTotalAllowancesAttribute(): float
    {
        return (float) $this->staff->sum(
            function ($staff) {

                return $this->pivotValue(
                    $staff,
                    'allowances',
                    0
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Payroll Summary
    |--------------------------------------------------------------------------
    */

    public function getPayrollSummaryAttribute(): array
    {
        $staff = $this->staff;


        $totalGross = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'gross_pay',
                    0
                );
            }
        );


        $totalNet = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'net_pay',
                    0
                );
            }
        );


        $totalTax = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'tax',
                    0
                );
            }
        );


        $totalPension = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'pension',
                    0
                );
            }
        );


        $totalDeductions = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'deductions',
                    0
                );
            }
        );


        $totalAllowances = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'allowances',
                    0
                );
            }
        );


        $totalOvertime = $staff->sum(
            function ($member) {

                return $this->pivotValue(
                    $member,
                    'overtime',
                    0
                );
            }
        );


        return [

            'total_staff' =>
                $staff->count(),

            'total_gross' =>
                (float) $totalGross,

            'total_net' =>
                (float) $totalNet,

            'total_tax' =>
                (float) $totalTax,

            'total_pension' =>
                (float) $totalPension,

            'total_deductions' =>
                (float) $totalDeductions,

            'total_allowances' =>
                (float) $totalAllowances,

            'total_overtime' =>
                (float) $totalOvertime,

            'avg_salary' =>
                $staff->count() > 0
                    ? (float) (
                        $totalNet / $staff->count()
                    )
                    : 0,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Month Helpers
    |--------------------------------------------------------------------------
    */

    public function getMonthNameAttribute(): string
    {
        return self::getMonths()[$this->month]
            ?? 'Unknown';
    }


    public function getDateRangeAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return 'N/A';
        }


        return $this->start_date->format('M d, Y')
            . ' - '
            . $this->end_date->format('M d, Y');
    }


    public function getFullPeriodLabelAttribute(): string
    {
        $academicYear = $this->academicYear
            ? $this->academicYear->name
            : '';


        return trim(
            $academicYear
            . ' - '
            . $this->month_name
            . ' '
            . $this->year
        );
    }


    public function getDisplayNameAttribute(): string
    {
        return $this->month_name
            . ' '
            . $this->year
            . ' ('
            . (
                $this->academicYear
                    ? $this->academicYear->name
                    : 'N/A'
            )
            . ')';
    }


    /*
    |--------------------------------------------------------------------------
    | Status Display
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status]
            ?? 'Unknown';
    }


    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {

            self::STATUS_DRAFT =>
                'badge-secondary',

            self::STATUS_PROCESSING =>
                'badge-info',

            self::STATUS_PENDING_APPROVAL =>
                'badge-warning',

            self::STATUS_APPROVED =>
                'badge-success',

            self::STATUS_REJECTED =>
                'badge-danger',

            self::STATUS_PAID =>
                'badge-primary',

            self::STATUS_CANCELLED =>
                'badge-dark',

            default =>
                'badge-secondary',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Period Code
    |--------------------------------------------------------------------------
    */

    public static function generatePeriodCode(): string
    {
        $year = now()->format('Y');

        $month = now()->format('m');


        $lastPeriod = self::whereYear(
            'created_at',
            $year
        )
        ->whereMonth(
            'created_at',
            $month
        )
        ->orderByDesc('id')
        ->first();


        if ($lastPeriod) {

            $lastNumber = intval(
                substr(
                    $lastPeriod->period_code,
                    -4
                )
            );

            $newNumber = str_pad(
                $lastNumber + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

        } else {

            $newNumber = '0001';
        }


        return 'PR-'
            . $year
            . $month
            . '-'
            . $newNumber;
    }
}