<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassProgression extends Model
{
    protected $fillable = [
        'from_class_id',
        'to_class_id',
        'is_graduation'
    ];

    public function fromClass()
    {
        return $this->belongsTo(
            StudentClass::class,
            'from_class_id'
        );
    }

    public function toClass()
    {
        return $this->belongsTo(
            StudentClass::class,
            'to_class_id'
        );
    }

    public function progressionFrom()
    {
    return $this->hasOne(
    ClassProgression::class,
    'from_class_id'
    );
    }
    
    public function progressionTo()
    {
    return $this->hasMany(
    ClassProgression::class,
    'to_class_id'
    );
    }
    
}
