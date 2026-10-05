<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    protected $fillable = [
        'household_id', 'farm_code', 'farm_name', 'address',
        'province_id', 'district_id', 'tambon_id',
        'total_area_rai', 'gps_lat', 'gps_lng', 'water_source', 'status',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function tambon()
    {
        return $this->belongsTo(Tambon::class);
    }
}
