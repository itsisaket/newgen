<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
    protected $fillable = ['category', 'name'];

    public const CATEGORIES = [
        'fertilizer', 'chemical', 'labor', 'water_energy', 'biomass_product', 'harvest_transport',
    ];

    public function farmActivities()
    {
        return $this->hasMany(FarmActivity::class);
    }
}
