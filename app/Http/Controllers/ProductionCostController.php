<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;
use App\Models\Household;
use App\Services\AreaScopeService;
use App\Services\ProductionCostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F02-lite - Production & Cost report (Blueprint section 6 F02 / 7.2). See
 * App\Services\ProductionCostService's doc-comment for exactly what this
 * does and doesn't compute yet (no Cost/kg - no harvest data entry screen
 * exists yet to compute it from).
 *
 * Read-only, so it reuses HouseholdPolicy's `view`/`viewAny` check (the
 * same "can this user see this household's data" rule every other
 * F01-F15 screen uses) rather than a dedicated policy of its own.
 */
class ProductionCostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Cross-household overview (23 ก.ย. round - see
     * DRFIS-Workflow-Menu-Analysis-23Sep.md ข้อ 3: previously there was no
     * way to see F02 across households at all, only one at a time via
     * show()). Recomputes summarizeHousehold() per row on every page load,
     * same "always fresh, never cached" convention as show() - fine at
     * the 20-per-page pagination size used here.
     */
    public function index(Request $request, AreaScopeService $areaScope, ProductionCostService $service)
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

        $rows = $households->getCollection()->map(fn ($household) => [
            'household' => $household,
            'summary' => $service->summarizeHousehold($household, $season),
        ]);

        return view('production-costs.index', [
            'rows' => $rows,
            'households' => $households,
            'season' => $season,
            'seasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
        ]);
    }

    public function show(Household $household, Request $request, ProductionCostService $service)
    {
        $this->authorize('view', $household);

        $seasonId = $request->integer('crop_season_id') ?: null;
        $season = $seasonId
            ? CropSeason::find($seasonId)
            : CropSeason::where('status', 'active')->first();

        $summary = $service->summarizeHousehold($household, $season);

        return view('households.production-cost', [
            'household' => $household,
            'season' => $season,
            'seasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
            'summary' => $summary,
        ]);
    }
}
