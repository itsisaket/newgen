<?php

namespace App\Models;

use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;

class KilnBatch extends Model
{
    protected $fillable = [
        'technology_asset_id', 'household_id', 'batch_code', 'operator_id', 'batch_date',
        'biomass_input_kg', 'labor_cost', 'energy_cost', 'other_cost', 'production_time_hours',
        'status', 'rejection_reason', 'recorded_by', 'original_record_id',
    ];

    protected $attributes = [
        'status' => WorkflowStatus::DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'batch_date' => 'date',
            'biomass_input_kg' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'energy_cost' => 'decimal:2',
            'other_cost' => 'decimal:2',
            'production_time_hours' => 'decimal:2',
        ];
    }

    public function technologyAsset()
    {
        return $this->belongsTo(TechnologyAsset::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function outputs()
    {
        return $this->hasMany(KilnBatchOutput::class);
    }

    public function evidences()
    {
        return $this->morphMany(Evidence::class, 'evidenceable');
    }

    /**
     * Total cost for the batch (Blueprint 8.2 "Cost per kg Biochar" /
     * "Cost per liter Wood Vinegar" denominators).
     */
    public function totalCost(): float
    {
        return (float) $this->labor_cost + (float) $this->energy_cost + (float) $this->other_cost;
    }
}
