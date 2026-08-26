<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class StudentClassAssignment extends Model
{
  

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'student_class_id',
        'academic_year_id',
        'status',
        'assigned_date',
        'promotion_date',
        'is_current',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'assigned_date' => 'datetime',
        'promotion_date' => 'datetime',
        'is_current' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
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

    /**
     * Get the student associated with this assignment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the student class associated with this assignment.
     */
    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    /**
     * Get the academic year associated with this assignment.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the user who created this assignment.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this assignment.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all bill sheets associated with this assignment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
   

    /**
     * Get the active bill sheet for this assignment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activeBillSheet()
    {
        return $this->hasOne(BillSheet::class, 'student_class_assignment_id')
            ->where('is_active', true)
            ->where('status', 'approved')
            ->latest();
    }

    /**
     * Get the latest bill sheet for this assignment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestBillSheet()
    {
        return $this->hasOne(BillSheet::class, 'student_class_assignment_id')
            ->latest();
    }

    /**
     * Get all bill sheet items through bill sheets.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function billSheetItems()
    {
        return $this->hasManyThrough(
            BillSheetItem::class,
            BillSheet::class,
            'student_class_assignment_id', // Foreign key on bill_sheets table
            'bill_sheet_id',               // Foreign key on bill_sheet_items table
            'id',                          // Local key on student_class_assignments table
            'id'                           // Local key on bill_sheets table
        );
    }

    /**
     * Get all fee items through bill sheets.
     * Alias for billSheetItems()
     */
    public function feeItems()
    {
        return $this->billSheetItems();
    }

    /**
     * Get the total amount of all bill sheets for this assignment.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function totalBillSheetAmount()
    {
        return $this->billSheets()->sum('net_amount');
    }

    /**
     * Get the total amount of all bill sheet items for this assignment.
     * 
     * @return float
     */
    public function getTotalBillSheetItemsAmountAttribute()
    {
        return $this->billSheetItems()->sum('total_amount');
    }

    /**
     * Get the count of bill sheets for this assignment.
     * 
     * @return int
     */
    public function getBillSheetCountAttribute()
    {
        return $this->billSheets()->count();
    }

    /**
     * Get the count of active bill sheets for this assignment.
     * 
     * @return int
     */
    public function getActiveBillSheetCountAttribute()
    {
        return $this->billSheets()->where('is_active', true)->count();
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include current assignments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope a query to only include active assignments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending assignments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed assignments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include assignments for a specific student.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include assignments for a specific class.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $classId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('student_class_id', $classId);
    }

    /**
     * Scope a query to only include assignments for a specific academic year.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $yearId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    /**
     * Scope a query to only include assignments with bill sheets.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasBillSheets($query)
    {
        return $query->has('billSheets');
    }

    /**
     * Scope a query to only include assignments without bill sheets.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutBillSheets($query)
    {
        return $query->doesntHave('billSheets');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the formatted assignment date.
     * 
     * @return string
     */
    public function getFormattedAssignedDateAttribute()
    {
        return $this->assigned_date ? $this->assigned_date->format('d M, Y') : 'N/A';
    }

    /**
     * Get the formatted promotion date.
     * 
     * @return string
     */
    public function getFormattedPromotionDateAttribute()
    {
        return $this->promotion_date ? $this->promotion_date->format('d M, Y') : 'N/A';
    }

    /**
     * Get the status badge class.
     * 
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        $statuses = [
            'pending' => 'warning',
            'active' => 'success',
            'completed' => 'info',
            'cancelled' => 'danger',
            'archived' => 'secondary',
        ];

        return $statuses[$this->status] ?? 'secondary';
    }

    /**
     * Get the status label.
     * 
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status ?? 'Unknown');
    }

    /**
     * Get the assignment display name.
     * 
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        $studentName = $this->student ? $this->student->full_name : 'Unknown Student';
        $className = $this->studentClass ? $this->studentClass->name : 'Unknown Class';
        $yearName = $this->academicYear ? $this->academicYear->name : 'Unknown Year';
        
        return "{$studentName} - {$className} ({$yearName})";
    }

    /**
     * Get the assignment summary.
     * 
     * @return string
     */
    public function getSummaryAttribute()
    {
        $className = $this->studentClass ? $this->studentClass->name : 'Unknown Class';
        $yearName = $this->academicYear ? $this->academicYear->name : 'Unknown Year';
        
        return "{$className} - {$yearName}";
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the assignment has bill sheets.
     * 
     * @return bool
     */
    public function hasBillSheets()
    {
        return $this->billSheets()->exists();
    }

    /**
     * Check if the assignment has an active bill sheet.
     * 
     * @return bool
     */
    public function hasActiveBillSheet()
    {
        return $this->activeBillSheet()->exists();
    }

    /**
     * Get the total amount due for this assignment.
     * 
     * @return float
     */
    public function getTotalAmountDue()
    {
        return $this->billSheets()->sum('net_amount');
    }

    /**
     * Get the total amount paid for this assignment.
     * 
     * @return float
     */
    public function getTotalAmountPaid()
    {
        // This assumes you have a payments relationship
        // You can customize this based on your payment structure
        return $this->billSheets()->sum('amount_paid') ?? 0;
    }

    /**
     * Get the balance due for this assignment.
     * 
     * @return float
     */
    public function getBalanceDue()
    {
        return $this->getTotalAmountDue() - $this->getTotalAmountPaid();
    }

    /**
     * Check if the assignment is current.
     * 
     * @return bool
     */
    public function isCurrent()
    {
        return $this->is_current && $this->status === 'active';
    }

    /**
     * Check if the assignment is overdue.
     * 
     * @return bool
     */
    public function isOverdue()
    {
        if (!$this->promotion_date) {
            return false;
        }
        
        return $this->promotion_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Mark the assignment as completed.
     * 
     * @return bool
     */
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->is_current = false;
        return $this->save();
    }

    /**
     * Mark the assignment as active.
     * 
     * @return bool
     */
    public function markAsActive()
    {
        $this->status = 'active';
        $this->is_current = true;
        return $this->save();
    }

    /**
     * Get all bill sheet items grouped by category.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getItemsGroupedByCategory()
    {
        return $this->billSheetItems()
            ->with('feeCategory')
            ->get()
            ->groupBy('fee_category_id')
            ->map(function ($items) {
                return [
                    'category' => $items->first()->feeCategory->name ?? 'Uncategorized',
                    'items' => $items,
                    'total' => $items->sum('total_amount'),
                ];
            });
    }

    /**
     * Get the assignment with all related data for display.
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getWithRelations()
    {
        return self::with([
            'student',
            'studentClass',
            'academicYear',
            'billSheets',
            'billSheets.items',
            'billSheets.items.feeCategory',
            'createdBy',
            'updatedBy',
        ]);
    }

    /**
     * Get the total count of students in this assignment's class.
     * 
     * @return int
     */
    public function getClassStudentCount()
    {
        if (!$this->studentClass) {
            return 0;
        }
        
        return $this->studentClass->students()->count();
    }

    /**
     * Check if the assignment is for the current academic year.
     * 
     * @return bool
     */
    public function isCurrentAcademicYear()
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        if (!$currentYear) {
            return false;
        }
        
        return $this->academic_year_id === $currentYear->id;
    }

    /**
     * Get the assignment status with icon.
     * 
     * @return string
     */
    public function getStatusWithIconAttribute()
    {
        $icons = [
            'pending' => '⏳',
            'active' => '✅',
            'completed' => '✔️',
            'cancelled' => '❌',
            'archived' => '📁',
        ];
        
        $icon = $icons[$this->status] ?? '📌';
        $label = $this->status_label;
        
        return "{$icon} {$label}";
    }

    /**
     * Get the total number of bill sheet items for this assignment.
     * 
     * @return int
     */
    public function getTotalBillSheetItemsCountAttribute()
    {
        return $this->billSheetItems()->count();
    }

    /**
     * Get the average amount per bill sheet item.
     * 
     * @return float
     */
    public function getAverageItemAmountAttribute()
    {
        $totalItems = $this->billSheetItems()->count();
        
        if ($totalItems === 0) {
            return 0;
        }
        
        return $this->getTotalBillSheetItemsAmountAttribute() / $totalItems;
    }

    public function billSheets()
    {
        return $this->hasMany(
            BillSheet::class,
            'student_class_assignment_id'
        );
    }

        
}