<?php

namespace App\Services;

use App\Exceptions\CarbonCalculationException;
use App\Models\CarbonActivity;
use App\Models\CarbonCalculation;
use App\Models\EmissionFactor;

/**
 * F15 - Carbon Activity Monitoring, calculation engine (Blueprint Appendix
 * C.6, same "Service is the only writer" convention as EconomicImpactService/
 * InventoryLedgerService). Called ONLY from CarbonActivityController::approve(),
 * mirroring exactly how KilnBatchController::approve() is the only caller of
 * InventoryLedgerService::record() - an unverified CarbonActivity must never
 * produce a published CO2e figure.
 *
 *   CO2e (kg) = quantity (ตามหน่วยของกิจกรรม) x factor_value ของ Emission
 *               Factor ที่ effective ณ วันที่กิจกรรมเกิดขึ้น (activity_date)
 */
class CarbonCalculationService
{
    public const CALCULATION_VERSION = 'v1';

    public function calculateFor(CarbonActivity $activity): CarbonCalculation
    {
        $factor = EmissionFactor::effectiveFor($activity->category, $activity->activity_date);

        if (! $factor) {
            throw new CarbonCalculationException(
                'ไม่พบค่าสัมประสิทธิ์การปล่อยก๊าซเรือนกระจก (Emission Factor) ที่ใช้งานได้สำหรับหมวดหมู่ ['
                .($activity->categoryLabel())
                .'] ณ วันที่ '.$activity->activity_date->format('d/m/Y')
                .' กรุณาเพิ่มค่าสัมประสิทธิ์ให้หมวดหมู่นี้ก่อน (เมนู "ค่าสัมประสิทธิ์การปล่อยก๊าซ")'
            );
        }

        $co2eKg = round((float) $activity->quantity * (float) $factor->factor_value, 4);

        return CarbonCalculation::updateOrCreate(
            ['carbon_activity_id' => $activity->id],
            [
                'emission_factor_id' => $factor->id,
                'co2e_kg' => $co2eKg,
                // snapshot ค่า EF ณ วันคำนวณ - ย้อนตรวจได้แม้ emission_factors ถูกแก้ภายหลัง
                'factor_value_snapshot' => $factor->factor_value,
                'factor_unit_snapshot' => $factor->unit,
                'factor_source_snapshot' => $factor->source,
                'calculation_version' => self::CALCULATION_VERSION,
                'calculated_at' => now(),
            ]
        );
    }
}
