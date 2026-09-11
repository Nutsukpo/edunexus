<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class StudentClassAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'student_class_id',
        'academic_year_id',
        'term_id',
        'status',
        'assigned_date',
        'promotion_date',
        'is_current',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_date' => 'datetime',
        'promotion_date' => 'datetime',
        'is_current' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = [
        'assigned_date',
        'promotion_date',
        'created_at',
        'updated_at',
        'deleted_at',
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

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | BILL SHEETS
    |--------------------------------------------------------------------------
    */

    public function billSheets()
    {
        return $this->hasMany(
            BillSheet::class,
            'student_class_assignment_id'
        );
    }

    public function activeBillSheet()
    {
        return $this->hasOne(
            BillSheet::class,
            'student_class_assignment_id'
        )
            ->where('is_active', true)
            ->where('status', 'approved')
            ->latest();
    }

    public function latestBillSheet()
    {
        return $this->hasOne(
            BillSheet::class,
            'student_class_assignment_id'
        )->latest();
    }

    public function billSheetItems()
    {
        return $this->hasManyThrough(
            BillSheetItem::class,
            BillSheet::class,
            'student_class_assignment_id',
            'bill_sheet_id',
            'id',
            'id'
        );
    }

    public function feeItems()
    {
        return $this->billSheetItems();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getItemsGroupedByCategory()
    {
        return $this->billSheetItems()
            ->with('feeCategory')
            ->get()
            ->groupBy('fee_category_id')
            ->map(function ($items) {
                return [
                    'category' => $items->first()
                        ->feeCategory
                        ->name ?? 'Uncategorized',

                    'items' => $items,

                    'total' => $items->sum('total_amount'),
                ];
            });
    }

    public static function getWithRelations()
    {
        return self::with([
            'student',
            'studentClass',
            'academicYear',
            'term',
            'billSheets',
            'billSheets.items',
            'billSheets.items.feeCategory',
            'createdBy',
            'updatedBy',
        ]);
    }

    public function getClassStudentCount()
    {
        if (!$this->studentClass) {
            return 0;
        }

        return $this->studentClass
            ->students()
            ->count();
    }

    public function isCurrentAcademicYear()
    {
        $currentYear = AcademicYear::where(
            'is_current',
            true
        )->first();

        if (!$currentYear) {
            return false;
        }

        return $this->academic_year_id === $currentYear->id;
    }

    public function getStatusWithIconAttribute()
    {
        $icons = [
            'pending' => '⏳',
            'active' => '✅',
            'completed' => '✔️',
            'promoted' => '⬆️',
            'repeated' => '🔁',
            'transferred' => '↔️',
            'graduated' => '🎓',
            'cancelled' => '❌',
            'archived' => '📁',
        ];

        $icon = $icons[$this->status] ?? '📌';

        $label = $this->status_label ?? ucfirst(
            str_replace('_', ' ', $this->status)
        );

        return "{$icon} {$label}";
    }

    public function getTotalBillSheetItemsCountAttribute()
    {
        return $this->billSheetItems()->count();
    }

    public function getAverageItemAmountAttribute()
    {
        $totalItems = $this->billSheetItems()->count();

        if ($totalItems === 0) {
            return 0;
        }

        return $this->getTotalBillSheetItemsAmountAttribute()
            / $totalItems;
    }
    
}