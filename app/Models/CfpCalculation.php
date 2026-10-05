<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CFP ระดับแปลง x ฤดูผลิต (ISO 14067) - service-exclusive write
 * (CfpCalculationService จะเป็นผู้เขียนเพียงผู้เดียว; ยังไม่ได้สร้างในรอบนี้)
 */
class CfpCalculation extends Model
{
    public const STATUS_ESTIMATED = 'estimated';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_CERTIFIED = 'certified';

    public const BOUNDARY_FARM_GATE = 'cradle_to_farm_gate';

    protected $fillable = [
        'plot_id', 'crop_season_id', 'plot_production_cycle_id', 'period_start', 'period_end', 'boundary', 'functional_unit', 'harvest_kg', 'area_rai',
        'e_total_kgco2e', 'cfp_per_kg', 'cfp_per_rai', 'breakdown_json', 'ef_snapshot_json',
        'gwp_version', 'data_quality_score', 'warnings_json', 'sensitivity_json', 'status', 'calculation_version', 'calculated_by', 'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'harvest_kg' => 'decimal:2',
            'area_rai' => 'decimal:2',
            'e_total_kgco2e' => 'decimal:4',
            'cfp_per_kg' => 'decimal:6',
            'cfp_per_rai' => 'decimal:4',
            'breakdown_json' => 'array',
            'ef_snapshot_json' => 'array',
            'period_start' => 'date',
            'period_end' => 'date',
            'data_quality_score' => 'decimal:2',
            'warnings_json' => 'array',
            'sensitivity_json' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function cycle()
    {
        return $this->belongsTo(PlotProductionCycle::class, 'plot_production_cycle_id');
    }

    public function cropSeason()
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function calculatedBy()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
