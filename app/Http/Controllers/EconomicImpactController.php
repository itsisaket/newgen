<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Household;
use App\Services\AreaScopeService;
use App\Services\EconomicImpactService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F06 - Economic Impact report (Blueprint หัวข้อ 11). Read-only screen,
 * same "reuse HouseholdPolicy::view()/viewAny()" convention as
 * ProductionCostController - but unlike that report, economic_impacts
 * (Blueprint's own Data Dictionary) is per-season, not "all seasons
 * combined", so a crop season is always required here; opening this page
 * is what triggers EconomicImpactService to (re)calculate and persist the
 * snapshot for that household+season - there's no separate form, matching
 * "ห้าม insert/update ตรงจากผู้ใช้".
 */
class EconomicImpactController extends Controller
{
    use AuthorizesRequests;

    /**
     * Cross-household overview (23 ก.ย. round - see
     * DRFIS-Workflow-Menu-Analysis-23Sep.md ข้อ 3). A season MUST be
     * selected before anything is calculated - unlike production-costs'
     * index this can't default to "all seasons combined" since
     * economic_impacts rows are always season-scoped. Calling
     * calculateForHousehold() per row here recalculates AND persists the
     * economic_impacts snapshot for every household on the page, same as
     * opening each household's F06 tab individually would.
     */
    public function index(Request $request, AreaScopeService $areaScope, EconomicImpactService $service)
    {
        $this->authorize('viewAny', Household::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $seasonId = $request->integer('crop_season_id') ?: null;
        $season = $seasonId
            ? CropSeason::find($seasonId)
            : CropSeason::where('status', 'active')->first();

        $households = Household::with(['farmerGroup', 'village'])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))
            ->orderBy('household_code')
            ->paginate(20)
            ->withQueryString();

        $rows = $season
            ? $households->getCollection()->map(fn ($household) => [
                'household' => $household,
                'impact' => $service->calculateForHousehold($household, $season),
            ])
            : collect();

        return view('economic-impacts.index', [
            'rows' => $rows,
            'households' => $households,
            'season' => $season,
            'seasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
            'targetBaht' => (float) config('drfis.economic_impact_target_baht', 60000),
        ]);
    }

    public function show(Household $household, Request $request, EconomicImpactService $service)
    {
        $this->authorize('view', $household);

        $seasonId = $request->integer('crop_season_id') ?: null;
        $season = $seasonId
            ? CropSeason::findOrFail($seasonId)
            : CropSeason::where('status', 'active')->first();

        $impact = $season ? $service->calculateForHousehold($household, $season) : null;

        return view('households.economic-impact', [
            'household' => $household,
            'season' => $season,
            'seasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
            'impact' => $impact,
            'targetBaht' => (float) config('drfis.economic_impact_target_baht', 60000),
        ]);
    }
}
