<?php

namespace App\Http\Controllers;

use App\Models\AlpAssessment;
use App\Models\CarbonActivity;
use App\Models\CarbonCalculation;
use App\Models\CropSeason;
use App\Models\DurianPhenologyRecord;
use App\Models\EconomicImpact;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\HarvestRecord;
use App\Models\Household;
use App\Models\InventoryTransaction;
use App\Models\KilnBatch;
use App\Models\KilnBatchOutput;
use App\Models\Plot;
use App\Models\Role;
use App\Models\Sale;
use App\Models\TechnologyAssignment;
use App\Services\AreaScopeService;
use App\Services\DataCompletenessService;
use App\Services\ProductionCostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Internal Dashboard suite (Blueprint หัวข้อ 14) - the 6 views the user
 * picked in the Sprint 5 round ("Dashboard เต็มรูปแบบ 6 มุมมอง"): Research,
 * Farm Management, Cost, Biomass, Durian Supply Forecast, Carbon. See
 * claude project doc DRFIS-Sprint5-Design-16Sep.md ส่วน C for the metric
 * list per view and why these 6 (not the public DashboardController's
 * aggregate overview, which already covers an Executive-style summary,
 * and not Innovator since F07/F09 full scope wasn't selected this round).
 *
 * Access (design doc ส่วน A, กลุ่ม 7 Dashboard/Report): every role except
 * Farmer/Innovator (Role::OWN_HOUSEHOLD - they see their own household
 * page instead, which already has its own summary cards). Area scope
 * follows AreaScopeService for STAFF, but Evaluator/Viewer get an
 * exception here specifically: the matrix gives them R* (all areas) for
 * Dashboard/Report even though they have no access at all to the
 * underlying registry screens - scopedHouseholdIds() below is the one
 * place that exception is implemented, deliberately NOT inside
 * AreaScopeService itself (which every other Policy also depends on and
 * must keep restricting Evaluator/Viewer to nothing).
 */
class InternalDashboardController extends Controller
{
    private function ensureCanViewDashboards(): void
    {
        abort_if(Auth::user()->hasAnyRole(Role::OWN_HOUSEHOLD), 403);
    }

    /**
     * @return array<int>|null null = ทุกพื้นที่ (ไม่จำกัดขอบเขต)
     */
    private function scopedHouseholdIds(AreaScopeService $areaScope): ?array
    {
        $user = Auth::user();

        if ($user->hasAnyRole(Role::READ_ONLY)) {
            return null;
        }

        return $areaScope->householdIdsFor($user);
    }

    /**
     * Every dashboard filters by one crop season (default: the active
     * one, or the most recent if none is marked active) - a consistent
     * selector across all 6 views rather than each inventing its own.
     */
    private function resolveSeason(Request $request): ?CropSeason
    {
        if ($request->filled('crop_season_id')) {
            return CropSeason::find($request->integer('crop_season_id'));
        }

        return CropSeason::where('status', 'active')->first() ?? CropSeason::orderByDesc('start_date')->first();
    }

    public function research(Request $request, AreaScopeService $areaScope, DataCompletenessService $completeness)
    {
        $this->ensureCanViewDashboards();

        $householdIds = $this->scopedHouseholdIds($areaScope);
        $season = $this->resolveSeason($request);
        $seasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        $baselineQuery = \App\Models\HouseholdBaseline::whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id));

        $baselineCount = (clone $baselineQuery)->count();
        $avgBaselineCost = (clone $baselineQuery)->avg('total_cost');
        $avgBaselineIncome = (clone $baselineQuery)->avg('total_income');

        $householdTotal = Household::when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))->count();

        $adoptedCount = Household::when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))
            ->whereHas('technologyAssignments', fn ($q) => $q->where('status', 'active'))
            ->count();
        $adoptionPct = $householdTotal > 0 ? round($adoptedCount / $householdTotal * 100, 1) : 0.0;

        $alpQuery = AlpAssessment::when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds));
        $alpAvgLevel = (clone $alpQuery)->avg('alp_level');
        $alpByLevel = (clone $alpQuery)->selectRaw('alp_level, count(*) as total')->groupBy('alp_level')->pluck('total', 'alp_level');

        $dataCompleteness = $completeness->summarize($householdIds);

        $impactQuery = EconomicImpact::when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id));
        $impactTotal = (clone $impactQuery)->count();
        $impactAchieved = (clone $impactQuery)->where('target_status', 'achieved')->count();
        $kpiAchievedPct = $impactTotal > 0 ? round($impactAchieved / $impactTotal * 100, 1) : null;

        return view('dashboards.research', [
            'seasons' => $seasons,
            'season' => $season,
            'baselineCount' => $baselineCount,
            'avgBaselineCost' => $avgBaselineCost,
            'avgBaselineIncome' => $avgBaselineIncome,
            'householdTotal' => $householdTotal,
            'adoptedCount' => $adoptedCount,
            'adoptionPct' => $adoptionPct,
            'alpAvgLevel' => $alpAvgLevel,
            'alpByLevel' => $alpByLevel,
            'dataCompleteness' => $dataCompleteness,
            'impactTotal' => $impactTotal,
            'impactAchieved' => $impactAchieved,
            'kpiAchievedPct' => $kpiAchievedPct,
        ]);
    }

    public function farmManagement(Request $request, AreaScopeService $areaScope)
    {
        $this->ensureCanViewDashboards();

        $householdIds = $this->scopedHouseholdIds($areaScope);
        $season = $this->resolveSeason($request);
        $seasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        $farms = Farm::with(['household', 'plots'])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->get();

        $totalAreaRai = (float) $farms->sum('total_area_rai');
        $plots = $farms->flatMap->plots;
        $totalTreeCount = (int) $plots->sum('tree_count');

        $harvestWeightKg = (float) HarvestRecord::whereHas('plot.farm', fn ($q) => $q->when($householdIds !== null, fn ($qq) => $qq->whereIn('household_id', $householdIds)))
            ->whereNull('original_record_id')
            ->where('status', 'approved')
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
            ->sum('actual_weight_kg');
        $yieldPerRai = $totalAreaRai > 0 ? round($harvestWeightKg / $totalAreaRai, 2) : null;

        $activities = FarmActivity::with('activityType')
            ->whereHas('plot.farm', fn ($q) => $q->when($householdIds !== null, fn ($qq) => $qq->whereIn('household_id', $householdIds)))
            ->whereNull('original_record_id')
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
            ->get();
        $activityStatusCounts = $activities->countBy('status');
        $inputByCategory = $activities->groupBy(fn ($a) => $a->activityType?->category ?? 'other')
            ->map(fn ($rows) => (float) $rows->sum('total_cost'));

        // Leaflet.js map (Blueprint 14's Farm Management dashboard) - only
        // farms with real coordinates recorded plot as a marker.
        $mapFarms = $farms->filter(fn (Farm $f) => $f->gps_lat !== null && $f->gps_lng !== null)
            ->map(fn (Farm $f) => [
                'lat' => (float) $f->gps_lat,
                'lng' => (float) $f->gps_lng,
                'label' => ($f->farm_name ?? $f->farm_code).' — '.($f->household->head_name ?? ''),
                'area_rai' => (float) ($f->total_area_rai ?? 0),
            ])
            ->values();

        return view('dashboards.farm-management', [
            'seasons' => $seasons,
            'season' => $season,
            'farmCount' => $farms->count(),
            'totalAreaRai' => $totalAreaRai,
            'plotCount' => $plots->count(),
            'totalTreeCount' => $totalTreeCount,
            'harvestWeightKg' => $harvestWeightKg,
            'yieldPerRai' => $yieldPerRai,
            'activityCount' => $activities->count(),
            'activityStatusCounts' => $activityStatusCounts,
            'inputByCategory' => $inputByCategory,
            'categoryLabels' => ProductionCostService::CATEGORY_LABELS,
            'mapFarms' => $mapFarms,
        ]);
    }

    public function cost(Request $request, AreaScopeService $areaScope, ProductionCostService $costService)
    {
        $this->ensureCanViewDashboards();

        $householdIds = $this->scopedHouseholdIds($areaScope);
        $season = $this->resolveSeason($request);
        $seasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        $households = Household::when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))->get();

        $rows = collect();
        $byCategoryTotal = collect();
        $costSavings = collect();

        foreach ($households as $household) {
            $summary = $costService->summarizeHousehold($household, $season);

            if ($summary['plot_count'] === 0) {
                continue;
            }

            $rows->push(['household' => $household, 'summary' => $summary]);

            foreach ($summary['by_category'] as $category => $amount) {
                $byCategoryTotal[$category] = ($byCategoryTotal[$category] ?? 0) + $amount;
            }

            if ($summary['cost_saving_vs_baseline'] !== null) {
                $costSavings->push($summary['cost_saving_vs_baseline']);
            }
        }

        $costPerRaiValues = $rows->pluck('summary.cost_per_rai')->filter(fn ($v) => $v !== null);
        $costPerTreeValues = $rows->pluck('summary.cost_per_tree')->filter(fn ($v) => $v !== null);
        $costPerKgValues = $rows->pluck('summary.cost_per_kg')->filter(fn ($v) => $v !== null);

        $topCostDriver = $byCategoryTotal->sortDesc()->keys()->first();

        return view('dashboards.cost', [
            'seasons' => $seasons,
            'season' => $season,
            'householdCount' => $rows->count(),
            'totalCost' => $rows->sum('summary.total_cost'),
            'avgCostPerRai' => $costPerRaiValues->isNotEmpty() ? round($costPerRaiValues->avg(), 2) : null,
            'avgCostPerTree' => $costPerTreeValues->isNotEmpty() ? round($costPerTreeValues->avg(), 2) : null,
            'avgCostPerKg' => $costPerKgValues->isNotEmpty() ? round($costPerKgValues->avg(), 2) : null,
            'byCategoryTotal' => $byCategoryTotal->sortDesc(),
            'categoryLabels' => ProductionCostService::CATEGORY_LABELS,
            'topCostDriver' => $topCostDriver,
            'avgCostSavingVsBaseline' => $costSavings->isNotEmpty() ? round($costSavings->avg(), 2) : null,
            'householdsWithSaving' => $costSavings->filter(fn ($v) => $v > 0)->count(),
            'rows' => $rows->sortByDesc('summary.total_cost')->take(20),
        ]);
    }

    public function biomass(Request $request, AreaScopeService $areaScope)
    {
        $this->ensureCanViewDashboards();

        $householdIds = $this->scopedHouseholdIds($areaScope);
        $season = $this->resolveSeason($request);
        $seasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        $batchQuery = KilnBatch::whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->when($season, fn ($q) => $q->whereBetween('batch_date', [$season->start_date, $season->end_date]));

        $batchCount = (clone $batchQuery)->count();
        $approvedBatchIds = (clone $batchQuery)->where('status', 'approved')->pluck('id');
        $inputKg = (clone $batchQuery)->where('status', 'approved')->sum('biomass_input_kg');

        $outputByProduct = KilnBatchOutput::whereIn('kiln_batch_id', $approvedBatchIds)
            ->with('product')
            ->get()
            ->groupBy(fn ($o) => $o->product->name ?? 'ไม่ระบุ')
            ->map(fn ($rows) => (float) $rows->sum('output_quantity'));

        $usageKg = InventoryTransaction::where('transaction_type', 'farm_use')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->when($season, fn ($q) => $q->whereBetween('transaction_date', [$season->start_date, $season->end_date]))
            ->sum('quantity');

        // Current stock = latest balance_after per product across in-scope
        // households, summed - same "latest transaction wins" convention
        // as HouseholdController/ProductController's own balance lookups.
        $latestIds = InventoryTransaction::when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->selectRaw('product_id, household_id, MAX(id) as last_id')
            ->groupBy('product_id', 'household_id')
            ->pluck('last_id');
        $stockByProduct = InventoryTransaction::whereIn('id', $latestIds)
            ->with('product')
            ->get()
            ->groupBy(fn ($t) => $t->product->name ?? 'ไม่ระบุ')
            ->map(fn ($rows) => (float) $rows->sum('balance_after'));

        $bioproductSales = Sale::where('product_type', 'bioproduct')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('seller_household_id', $householdIds))
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
            ->sum('total_amount');

        return view('dashboards.biomass', [
            'seasons' => $seasons,
            'season' => $season,
            'batchCount' => $batchCount,
            'inputKg' => (float) $inputKg,
            'outputByProduct' => $outputByProduct,
            'usageKg' => (float) $usageKg,
            'stockByProduct' => $stockByProduct,
            'bioproductSales' => (float) $bioproductSales,
        ]);
    }

    public function forecast(Request $request, AreaScopeService $areaScope)
    {
        $this->ensureCanViewDashboards();

        $householdIds = $this->scopedHouseholdIds($areaScope);
        $season = $this->resolveSeason($request);
        $seasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        // Latest phenology observation per plot for this season that
        // actually carries a forecast (expected_harvest_date/weight are
        // only filled in from the fruit_set/fruit_development stages
        // onward - see DurianPhenologyRecordController).
        $phenologyQuery = DurianPhenologyRecord::with(['plot.farm.household', 'plot.durianVariety'])
            ->whereHas('plot.farm', fn ($q) => $q->when($householdIds !== null, fn ($qq) => $qq->whereIn('household_id', $householdIds)))
            ->whereNotNull('expected_harvest_date')
            ->when($season, fn ($q) => $q->where('crop_season_id', $season->id));

        $forecasts = $phenologyQuery->get()
            ->groupBy('plot_id')
            ->map(fn ($rows) => $rows->sortByDesc('observed_date')->first());

        $expectedAvgWeight = $forecasts->pluck('expected_avg_weight_kg')->filter()->avg();

        $byWeek = $forecasts->groupBy(fn ($r) => $r->expected_harvest_date->format('Y-\\WW'))
            ->map->count()
            ->sortKeys();

        $byVariety = $forecasts->groupBy(fn ($r) => $r->plot->durianVariety->name ?? 'ไม่ระบุพันธุ์')
            ->map(fn ($rows) => [
                'plots' => $rows->count(),
                'area_rai' => (float) $rows->sum(fn ($r) => (float) ($r->plot->area_rai ?? 0)),
            ]);

        // Forecast Accuracy: plots that have BOTH a forecast (above) and
        // an approved actual harvest in the same season - % difference
        // between expected_avg_weight_kg x fruit_count (Blueprint's
        // "Expected kg/fruit" figure scaled to the plot) and the actual
        // approved harvest weight for that plot/season.
        $accuracyRows = collect();
        foreach ($forecasts as $plotId => $forecast) {
            if (! $forecast->expected_avg_weight_kg || ! $forecast->fruit_count) {
                continue;
            }

            $expectedTotalKg = (float) $forecast->expected_avg_weight_kg * (int) $forecast->fruit_count;

            $actualKg = (float) HarvestRecord::where('plot_id', $plotId)
                ->whereNull('original_record_id')
                ->where('status', 'approved')
                ->when($season, fn ($q) => $q->where('crop_season_id', $season->id))
                ->sum('actual_weight_kg');

            if ($actualKg <= 0) {
                continue;
            }

            $accuracyRows->push([
                'plot' => $forecast->plot,
                'expected_kg' => round($expectedTotalKg, 2),
                'actual_kg' => round($actualKg, 2),
                'variance_pct' => $expectedTotalKg > 0 ? round(($actualKg - $expectedTotalKg) / $expectedTotalKg * 100, 1) : null,
            ]);
        }

        $avgAbsVariancePct = $accuracyRows->isNotEmpty()
            ? round($accuracyRows->pluck('variance_pct')->filter(fn ($v) => $v !== null)->map(fn ($v) => abs($v))->avg(), 1)
            : null;

        return view('dashboards.forecast', [
            'seasons' => $seasons,
            'season' => $season,
            'forecastPlotCount' => $forecasts->count(),
            'expectedAvgWeight' => $expectedAvgWeight,
            'byWeek' => $byWeek,
            'byVariety' => $byVariety,
            'accuracyRows' => $accuracyRows->sortByDesc(fn ($r) => abs($r['variance_pct'] ?? 0))->take(15),
            'avgAbsVariancePct' => $avgAbsVariancePct,
        ]);
    }

    public function carbon(Request $request, AreaScopeService $areaScope)
    {
        $this->ensureCanViewDashboards();

        $householdIds = $this->scopedHouseholdIds($areaScope);
        $season = $this->resolveSeason($request);
        $seasons = CropSeason::orderByDesc('start_date')->get(['id', 'name']);

        $activityQuery = CarbonActivity::whereNull('original_record_id')
            ->where('status', 'approved')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->when($season, fn ($q) => $q->whereBetween('activity_date', [$season->start_date, $season->end_date]));

        $activities = (clone $activityQuery)->get();

        $quantityByCategory = $activities->groupBy('category')
            ->map(fn ($rows) => (float) $rows->sum('quantity'));

        $co2eByCategory = CarbonCalculation::whereIn('carbon_activity_id', $activities->pluck('id'))
            ->with('carbonActivity')
            ->get()
            ->groupBy(fn ($c) => $c->carbonActivity->category)
            ->map(fn ($rows) => (float) $rows->sum('co2e_kg'));

        $totalCo2eKg = $co2eByCategory->sum();

        return view('dashboards.carbon', [
            'seasons' => $seasons,
            'season' => $season,
            'activityCount' => $activities->count(),
            'quantityByCategory' => $quantityByCategory,
            'co2eByCategory' => $co2eByCategory,
            'totalCo2eKg' => $totalCo2eKg,
            'categoryLabels' => CarbonActivity::CATEGORY_LABELS,
            'fertilizerReductionQty' => $quantityByCategory->get(CarbonActivity::CATEGORY_REDUCED_CHEMICAL_FERTILIZER, 0)
                + $quantityByCategory->get(CarbonActivity::CATEGORY_ORGANIC_FERTILIZER, 0),
            'biomassDivertedQty' => $quantityByCategory->get(CarbonActivity::CATEGORY_BIOCHAR_APPLICATION, 0),
            'energyQty' => $quantityByCategory->get(CarbonActivity::CATEGORY_RENEWABLE_ENERGY, 0),
        ]);
    }
}
