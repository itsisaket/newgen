<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * F15 - service-exclusive write, see the carbon_calculations migration's
 * doc-comment. Never created/updated from a controller directly - only
 * CarbonCalculationService::calculateFor().
 */
class CarbonCalculation extends Model
{
    protected $fillable = [
        'carbon_activity_id', 'emission_factor_id', 'co2e_kg', 'factor_value_snapshot', 'factor_unit_snapshot',
        'factor_source_snapshot', 'calculation_version', 'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'co2e_kg' => 'decimal:4',
            'factor_value_snapshot' => 'decimal:6',
            'calculated_at' => 'datetime',
        ];
    }

    public function carbonActivity()
    {
        return $this->belongsTo(CarbonActivity::class);
    }

    public function emissionFactor()
    {
        return $this->belongsTo(EmissionFactor::class);
    }
}
