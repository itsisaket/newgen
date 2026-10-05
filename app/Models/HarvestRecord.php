<?php

namespace App\Models;

use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;

class HarvestRecord extends Model
{
    protected $fillable = [
        'plot_id', 'crop_season_id', 'harvest_date', 'actual_weight_kg',
        'grade', 'price_per_kg', 'buyer_id',
        'status', 'rejection_reason', 'recorded_by', 'original_record_id',
    ];

    protected $attributes = [
        'status' => WorkflowStatus::DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
            'actual_weight_kg' => 'decimal:2',
            'price_per_kg' => 'decimal:2',
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

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function evidences()
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }
}
