<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\EconomicImpact;
use App\Models\Household;
use App\Models\KilnBatch;
use App\Models\Sale;
use App\Support\WorkflowStatus;

/**
 * F06 - Economic Impact (Blueprint หัวข้อ 11). "F06 ควรเป็น Calculation Engine
 * ไม่ใช่แบบฟอร์มที่ผู้ใช้กรอกตัวเลขสรุปเอง ระบบดึงข้อมูลจาก Baseline, Farm Activity,
 * Harvest, Sales และ Technology Cost มาคำนวณอัตโนมัติ" - this is the ONE place
 * allowed to write economic_impacts (see that migration's doc-comment).
 *
 *   Net Benefit = Cost Saving + Durian Income Increase + Bioproduct Income
 *                 - Additional Technology Cost
 *
 *   | ผลลัพธ์                     | แหล่งข้อมูล                                    |
 *   |----------------------------|------------------------------------------------|
 *   | Cost Saving                | Baseline เทียบ Current Cost (ProductionCostService) |
 *   | Durian Income Increase     | Sales (product_type=durian) เทียบ Baseline income |
 *   | Bioproduct Income          | Sales (product_type=bioproduct)                 |
 *   | Additional Technology Cost | ต้นทุนการเดินเตาที่อนุมัติแล้วในฤดูนี้ (KilnBatch)   |
 *   | Target Status              | Achieved เมื่อ Net Benefit >= config('drfis.economic_impact_target_baht') |
 */
class EconomicImpactService
{
    public const CALCULATION_VERSION = 'v1';

    public function __construct(private ProductionCostService $productionCostService) {}

    public function calculateForHousehold(Household $household, CropSeason $cropSeason): EconomicImpact
    {
        // Cost Saving reuses ProductionCostService's own Before-After
        // comparison against the household's F01 Baseline for this same
        // season - never re-derived here, so the two reports can never
        // silently disagree.
        $costSummary = $this->productionCostService->summarizeHousehold($household, $cropSeason);
        $costSaving = (float) ($costSummary['cost_saving_vs_baseline'] ?? 0.0);
        $baseline = $costSummary['baseline'];

        $sales = Sale::where('seller_household_id', $household->id)
            ->where('crop_season_id', $cropSeason->id)
            ->get();

        $durianSalesTotal = (float) $sales->where('product_type', 'durian')->sum('total_amount');
        $bioproductIncome = (float) $sales->where('product_type', 'bioproduct')->sum('total_amount');

        // "Durian/Sales เทียบ Baseline" - the baseline's total_income
        // represents the household's pre-project durian income, so the
        // increase is this season's actual durian sales minus that.
        $baselineIncome = (float) ($baseline->total_income ?? 0.0);
        $durianIncomeIncrease = round($durianSalesTotal - $baselineIncome, 2);

        // "ต้นทุนการเดินเตา ค่าเสื่อม/ต้นทุนเพิ่ม" - only APPROVED batches count
        // (same "don't let an unverified number drive a published KPI"
        // rule ProductionCostService already applies to Cost/kg), matched
        // to the season by batch_date falling inside its date range since
        // kiln_batches has no crop_season_id column of its own.
        $additionalTechnologyCost = (float) KilnBatch::where('household_id', $household->id)
            ->whereNull('original_record_id')
            ->where('status', WorkflowStatus::APPROVED)
            ->whereBetween('batch_date', [$cropSeason->start_date, $cropSeason->end_date])
            ->get()
            ->sum(fn (KilnBatch $batch) => $batch->totalCost());

        $netBenefit = round($costSaving + $durianIncomeIncrease + $bioproductIncome - $additionalTechnologyCost, 2);

        $targetBaht = (float) config('drfis.economic_impact_target_baht', 60000);

        return EconomicImpact::updateOrCreate(
            ['household_id' => $household->id, 'crop_season_id' => $cropSeason->id],
            [
                'cost_saving' => round($costSaving, 2),
                'durian_income_increase' => $durianIncomeIncrease,
                'bioproduct_income' => round($bioproductIncome, 2),
                'additional_technology_cost' => round($additionalTechnologyCost, 2),
                'net_benefit' => $netBenefit,
                'target_status' => $netBenefit >= $targetBaht ? 'achieved' : 'not_achieved',
                'calculated_at' => now(),
                'calculation_version' => self::CALCULATION_VERSION,
            ]
        );
    }
}
