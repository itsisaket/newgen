<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInnovatorRequest;
use App\Models\Household;
use App\Models\Innovator;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * F07/F09/F11 Sprint 4 - master registry of innovators (Blueprint Appendix
 * C.3). Most rows are created automatically by
 * InnovatorEvaluationController::store() when a household's evaluation
 * passes - this controller's create/store is only for the Blueprint's
 * household_id-nullable case (a no-household innovator/community leader).
 * No edit/destroy - same "no hard delete, historical registry" convention
 * as Household/InnovatorEvaluation.
 */
class InnovatorController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', Innovator::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $innovators = Innovator::with('household')
            // A no-household row (household_id null) is only visible to
            // STAFF - see InnovatorPolicy::view(). Mirrors that same rule
            // here at the list level instead of filtering after paginate.
            ->when(
                $householdIds !== null,
                fn ($q) => $q->where(fn ($qq) => $qq->whereIn('household_id', $householdIds)
                    ->orWhere(fn ($qqq) => $qqq->whereNull('household_id')->when(
                        ! auth()->user()->hasAnyRole(\App\Models\Role::STAFF),
                        fn ($x) => $x->whereRaw('1 = 0')
                    ))),
            )
            ->orderBy('name')
            ->paginate(20);

        return view('innovators.index', compact('innovators'));
    }

    public function create()
    {
        $this->authorize('create', Innovator::class);

        return view('innovators.create', [
            // Only households not already registered as an innovator -
            // the normal path for those is the pass-evaluation flow, not
            // this form (StoreInnovatorRequest's unique rule enforces the
            // same thing server-side).
            'households' => Household::whereDoesntHave('innovator')->orderBy('head_name')->get(['id', 'head_name', 'household_code']),
        ]);
    }

    public function store(StoreInnovatorRequest $request)
    {
        $this->authorize('create', Innovator::class);

        $innovator = Innovator::create($request->validated());

        return redirect()->route('innovators.show', $innovator)->with('status', 'ลงทะเบียนนวัตกร/แกนนำเรียบร้อยแล้ว');
    }

    public function show(Innovator $innovator)
    {
        $this->authorize('view', $innovator);

        $innovator->load(['household', 'qualifyingEvaluation', 'competencyAssessments.scores.indicator']);

        $alpAssessments = $innovator->household_id
            ? $innovator->household->alpAssessments()->with('technology')->get()
            : collect();

        return view('innovators.show', ['innovator' => $innovator, 'alpAssessments' => $alpAssessments]);
    }
}
