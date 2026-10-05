<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tambon extends Model
{
    protected $fillable = ['district_id', 'name_th', 'name_en', 'zip_code'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function villages()
    {
        return $this->hasMany(Village::class);
    }

    public function farmerGroups()
    {
        return $this->hasMany(FarmerGroup::class);
    }
}
