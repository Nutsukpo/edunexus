<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'current_academic_year_id',
        'current_term_id',
        'operational_academic_year_id',
        'operational_term_id',
        'auto_assign_current_period',
        'use_current_period_defaults',
    ];

    protected $casts = [
        'current_academic_year_id' => 'integer',
        'current_term_id' => 'integer',
        'operational_academic_year_id' => 'integer',
        'operational_term_id' => 'integer',
        'auto_assign_current_period' => 'boolean',
        'use_current_period_defaults' => 'boolean',
    ];

    public function currentAcademicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'current_academic_year_id'
        );
    }

    public function currentTerm(): BelongsTo
    {
        return $this->belongsTo(
            Term::class,
            'current_term_id'
        );
    }

    public function operationalAcademicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'operational_academic_year_id'
        );
    }

    public function operationalTerm(): BelongsTo
    {
        return $this->belongsTo(
            Term::class,
            'operational_term_id'
        );
    }

    public static function instance(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'current_academic_year_id' => null,
                'current_term_id' => null,
                'operational_academic_year_id' => null,
                'operational_term_id' => null,
                'auto_assign_current_period' => true,
                'use_current_period_defaults' => true,
            ]
        );
    }
}
