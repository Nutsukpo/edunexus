<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'asset_code',
        'description',
        'category_id',
        'created_by',
        'updated_by',
        'serial_number',
        'model',
        'brand',
        'quantity',
        'purchase_price',
        'current_value',
        'purchase_date',
        'warranty_expiry',
        'location',
        'status',
        'condition',
        'image_path',
        'document_path',
        'document_name',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'metadata' => 'array',
        'quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->asset_code)) {
                $model->asset_code = self::generateAssetCode();
            }
        });
    }

    public static function generateAssetCode()
    {
        $prefix = 'AST';
        $year = date('Y');
        $random = Str::upper(Str::random(3));
        $code = $prefix . '-' . $year . '-' . $random;

        while (self::where('asset_code', $code)->exists()) {
            $random = Str::upper(Str::random(3));
            $code = $prefix . '-' . $year . '-' . $random;
        }

        return $code;
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class)->where('status', 'active');
    }

    public function maintenance()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'available' => 'success',
            'assigned' => 'primary',
            'maintenance' => 'warning',
            'damaged' => 'danger',
            'disposed' => 'secondary',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getConditionBadgeAttribute()
    {
        $badges = [
            'new' => 'success',
            'good' => 'primary',
            'fair' => 'warning',
            'poor' => 'danger',
            'damaged' => 'danger',
        ];
        return $badges[$this->condition] ?? 'secondary';
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->purchase_price, 2);
    }

    public function getDepreciationAttribute()
    {
        if ($this->purchase_price && $this->current_value) {
            return round((($this->purchase_price - $this->current_value) / $this->purchase_price) * 100);
        }
        return 0;
    }

    public function getIsUnderWarrantyAttribute()
    {
        if (!$this->warranty_expiry) {
            return false;
        }
        return $this->warranty_expiry->greaterThan(now());
    }

    // Helper Methods
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    public function assignTo($userId, $notes = null)
    {
        $assignment = $this->assignments()->create([
            'assigned_to' => $userId,
            'assigned_by' => auth()->id(),
            'assigned_date' => now(),
            'assignment_notes' => $notes,
            'status' => 'active',
        ]);

        $this->update(['status' => 'assigned']);

        return $assignment;
    }

    public function returnAsset($notes = null)
    {
        $assignment = $this->currentAssignment;
        if ($assignment) {
            $assignment->update([
                'actual_return_date' => now(),
                'return_notes' => $notes,
                'status' => 'returned',
            ]);
        }

        $this->update(['status' => 'available']);
    }

    public function markMaintenance()
    {
        $this->update(['status' => 'maintenance']);
    }

    public function markAvailable()
    {
        $this->update(['status' => 'available']);
    }

    public function markDamaged()
    {
        $this->update(['status' => 'damaged']);
    }

    public function dispose()
    {
        $this->update(['status' => 'disposed']);
    }
}