<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\FarmActivity;
use App\Models\HarvestRecord;
use App\Models\Household;
use App\Models\Plot;
use Illuminate\Support\Collection;

/**
 * F02-lite - Production & Cost (Blueprint section 6 F02 / 7.2 "ผลวิเคราะห์
 * ต้นทุน" / Appendix C.3 `farm_production_costs`).
 *
 * Blueprint's own Data Dictionary describes farm_production_costs as "a
 * Summary table computed from farm_activities by a Service, never entered
 * directly by the user" - this Service does exactly that computation, but
 * deliberately WITHOUT persisting it to a summary table: every number
 * here is derived fresh from farm_activities (F13) each time the report
 * is opened, rather than cached and risking going stale when an activity
 * is added/edited/approved after the summary was last computed. A
 * persisted table can be added later if performance ever requires it,
 * without changing what this Service returns.
 *
 * Cost/kg (F14/F08-lite round): now that harvest_records has a real entry
 * screen (HarvestRecordController), this only counts `approved` harvest
 * rows - a draft/submitted/verified harvest weight hasn't been checked
 * yet and using it here would let an unverified number silently drive a
 * published Cost/kg figure. Cost/Rai, Cost/Tree, cost by category
 * (Blueprint 7.2's 6 groups), and the Before-After comparison against the
 * household's F01 Baseline are unaffected by that and still computed the
 * same way as before.
 */
class ProductionCostService
{
    /**
     * Thai labels matching Blueprint section 7.2's cost-category table
     * exactly, keyed by activity_types.category (App\Models\ActivityType::CATEGORIES).
     */
    public const CATEGORY_LABELS = [
        'fertilizer' => 'ปุ๋ย/สารปรับปรุง',
        'chemical' => 'สารเคมี',
        'labor' => 'แรงงาน',
        'water_energy' => 'น้ำ/พลังงาน',
        'biomass_product' => 'Biochar/น้ำส้มควันไม้',
        'harvest_transport' => 'เก็บเกี่ยว/ขนส่ง',
    ];

    /**
     * @return array{total_cost: float, cost_per_rai: ?float, cost_per_tree: ?float, by_category: Collection<string, float>, activity_count: int, harvest_weight_kg: float, cost_per_kg: ?float}
     */
    public function summarizePlot(Plot $plot, ?CropSeason $season = null): array
    {
        $activities = FarmActivity::with('activityType')
            ->where('plot_id', $plot->id)
            ->whereNull('original_record_id')
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
            ->get();

        $totalCost = (float) $activities->sum('total_cost');

        $byCategory = $activities
            ->groupBy(fn (FarmActivity $a) => $a->activityType?->category ?? 'other')
            ->map(fn (Collection $rows) => (float) $rows->sum('total_cost'));

        $harvestWeightKg = (float) HarvestRecord::where('plot_id', $plot->id)
            ->whereNull('original_record_id')
            ->where('status', 'approved')
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
            ->sum('actual_weight_kg');

        return [
            'total_cost' => round($totalCost, 2),
            'cost_per_rai' => $plot->area_rai > 0 ? round($totalCost / (float) $plot->area_rai, 2) : null,
            'cost_per_tree' => $plot->tree_count > 0 ? round($totalCost / (int) $plot->tree_count, 2) : null,
            'by_category' => $byCategory,
            'activity_count' => $activities->count(),
            'harvest_weight_kg' => round($harvestWeightKg, 2),
            'cost_per_kg' => $harvestWeightKg > 0 ? round($totalCost / $harvestWeightKg, 2) : null,
        ];
    }

    /**
     * Rolls up summarizePlot() across every plot of every farm belonging
     * to $household, plus a "Before-After" comparison against its latest
     * F01 Baseline (Blueprint section 7.2's "Before-After" analysis
     * result) when one has been recorded for the same season.
     *
     * @return array{
     *   total_cost: float, cost_per_rai: ?float, cost_per_tree: ?float,
     *   by_category: Collection<string, float>, plot_count: int,
     *   total_area_rai: float, total_tree_count: int,
     *   harvest_weight_kg: float, cost_per_kg: ?float,
     *   plots: Collection<int, array>, baseline: ?\App\Models\HouseholdBaseline,
     *   cost_saving_vs_baseline: ?float
     * }
     */
    public function summarizeHousehold(Household $household, ?CropSeason $season = null): array
    {
        $plots = Plot::with('farm')
            ->whereHas('farm', fn ($q) => $q->where('household_id', $household->id))
            ->get();

        $totalCost = 0.0;
        $totalAreaRai = 0.0;
        $totalTreeCount = 0;
        $totalHarvestWeightKg = 0.0;
        $byCategory = collect();
        $plotSummaries = collect();

        foreach ($plots as $plot) {
            $summary = $this->summarizePlot($plot, $season);

            $totalCost += $summary['total_cost'];
            $totalAreaRai += (float) ($plot->area_rai ?? 0);
            $totalTreeCount += (int) ($plot->tree_count ?? 0);
            $totalHarvestWeightKg += $summary['harvest_weight_kg'];

            foreach ($summary['by_category'] as $category => $amount) {
                $byCategory[$category] = ($byCategory[$category] ?? 0) + $amount;
            }

            $plotSummaries->push([
                'plot' => $plot,
                'summary' => $summary,
            ]);
        }

        $baseline = $household->baselines()
            ->whereNull('original_record_id')
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
            ->latest()
            ->first();

        $costSavingVsBaseline = null;
        if ($baseline && $baseline->total_cost !== null) {
            $costSavingVsBaseline = round((float) $baseline->total_cost - $totalCost, 2);
        }

        return [
            'total_cost' => round($totalCost, 2),
            'cost_per_rai' => $totalAreaRai > 0 ? round($totalCost / $totalAreaRai, 2) : null,
            'cost_per_tree' => $totalTreeCount > 0 ? round($totalCost / $totalTreeCount, 2) : null,
            'by_category' => $byCategory,
            'plot_count' => $plots->count(),
            'total_area_rai' => round($totalAreaRai, 2),
            'total_tree_count' => $totalTreeCount,
            'harvest_weight_kg' => round($totalHarvestWeightKg, 2),
            'cost_per_kg' => $totalHarvestWeightKg > 0 ? round($totalCost / $totalHarvestWeightKg, 2) : null,
            'plots' => $plotSummaries,
            'baseline' => $baseline,
            'cost_saving_vs_baseline' => $costSavingVsBaseline,
        ];
    }
}
