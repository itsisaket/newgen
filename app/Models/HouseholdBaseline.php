<?php

namespace App\Models;

use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;

class HouseholdBaseline extends Model
{
    protected $fillable = [
        'household_id', 'crop_season_id', 'total_cost', 'total_income', 'baseline_area_rai', 'baseline_harvest_kg',
        'management_practice_notes', 'status', 'rejection_reason',
        'recorded_by', 'original_record_id',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
            'total_income' => 'decimal:2',
            'baseline_area_rai' => 'decimal:2',
            'baseline_harvest_kg' => 'decimal:2',
        ];
    }

    protected $attributes = [
        'status' => WorkflowStatus::DRAFT,
    ];

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function inputs()
    {
        return $this->hasMany(HouseholdBaselineInput::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function originalRecord()
    {
        return $this->belongsTo(self::class, 'original_record_id');
    }

    public function revisions()
    {
        return $this->hasMany(self::class, 'original_record_id');
    }
}
