<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurianPhenologyRecord extends Model
{
    protected $fillable = [
        'plot_id', 'crop_season_id', 'stage', 'observed_date',
        'fruit_count', 'expected_avg_weight_kg', 'expected_harvest_date', 'recorded_by',
    ];

    public const STAGES = ['flowering', 'full_bloom', 'fruit_set', 'fruit_development'];

    protected function casts(): array
    {
        return [
            'observed_date' => 'date',
            'expected_harvest_date' => 'date',
            'expected_avg_weight_kg' => 'decimal:2',
        ];
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
