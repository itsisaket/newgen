<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmerGroup extends Model
{
    protected $fillable = ['name', 'tambon_id', 'leader_name', 'contact_phone', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
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
