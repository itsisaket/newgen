<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F06 - see the migration's doc-comment. Read-only from the application's
 * perspective except App\Services\EconomicImpactService.
 */
class EconomicImpact extends Model
{
    protected $fillable = [
        'household_id', 'crop_season_id', 'cost_saving', 'durian_income_increase',
        'bioproduct_income', 'additional_technology_cost', 'net_benefit',
        'target_status', 'calculated_at', 'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'cost_saving' => 'decimal:2',
            'durian_income_increase' => 'decimal:2',
            'bioproduct_income' => 'decimal:2',
            'additional_technology_cost' => 'decimal:2',
            'net_benefit' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }
}
