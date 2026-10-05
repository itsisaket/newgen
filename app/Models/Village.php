<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = [
        'tambon_id', 'official_code', 'village_no', 'name_th',
        'latitude', 'longitude', 'source', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        if (! $this->village_no || $this->village_no >= 50) {
            return $this->name_th;
        }

        $name = str_starts_with($this->name_th, 'บ้าน') ? $this->name_th : 'บ้าน'.$this->name_th;

        return 'หมู่ '.$this->village_no.' '.$name;
    }

    public function tambon()
    {
        return $this->belongsTo(Tambon::class);
    }

    public function households()
    {
        return $this->hasMany(Household::class);
    }
}
