<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** การปล่อยสะสมช่วงก่อนให้ผลผลิตของแปลง (ไม้ยืนต้น) - ดู doc-comment ของ migration 2026_09_24_000005 */
class PlotPreBearingEmission extends Model
{
    public const BASIS_RECORDED = 'recorded';

    public const BASIS_ESTIMATED = 'estimated';

    protected $fillable = [
        'plot_id', 'total_kgco2e', 'data_basis', 'breakdown_json', 'note',
        'status', 'rejection_reason', 'recorded_by', 'original_record_id',
    ];

    protected function casts(): array
    {
        return [
            'total_kgco2e' => 'decimal:4',
            'breakdown_json' => 'array',
        ];
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
