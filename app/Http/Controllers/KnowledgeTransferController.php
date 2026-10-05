<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKnowledgeTransferRequest;
use App\Models\Innovator;
use App\Models\KnowledgeTransfer;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F11 Knowledge Transfer (Blueprint Appendix C.5). Innovator-locked
 * create (query param, like every other locked-parent module in this
 * codebase) - reachable from innovators/show.blade.php. No workflow
 * (Draft/Submitted/Verified/Approved) - a historical record of a
 * training/transfer event, same convention as InnovatorEvaluation/F13.
 * See KnowledgeTransferPolicy's doc-comment for the staff-only create
 * decision.
 */
class KnowledgeTransferController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', KnowledgeTransfer::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $transfers = KnowledgeTransfer::with(['innovator.household', 'recordedBy'])
            ->when(
                $householdIds !== null,
                fn ($q) => $q->whereHas('innovator', fn ($i) => $i->whereIn('household_id', $householdIds))
            )
            ->latest('transfer_date')
            ->paginate(20);

        return view('knowledge-transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $innovator = Innovator::find($request->integer('innovator_id'));

        if (! $innovator) {
            return redirect()
                ->route('innovators.index')
                ->with('status', 'กรุณาเปิดหน้านวัตกรที่ต้องการก่อน แล้วกดปุ่ม "+ เพิ่มการถ่ายทอดความรู้" จากหน้านั้น');
        }

        $this->authorize('create', [KnowledgeTransfer::class, $innovator]);

        return view('knowledge-transfers.create', ['innovator' => $innovator]);
    }

    public function store(StoreKnowledgeTransferRequest $request)
    {
        $innovator = Innovator::findOrFail($request->input('innovator_id'));
        $this->authorize('create', [KnowledgeTransfer::class, $innovator]);

        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        $transfer = KnowledgeTransfer::create($data);

        return redirect()->route('knowledge-transfers.show', $transfer)->with('status', 'บันทึกการถ่ายทอดความรู้เรียบร้อยแล้ว');
    }

    public function show(KnowledgeTransfer $knowledgeTransfer)
    {
        $this->authorize('view', $knowledgeTransfer);

        $knowledgeTransfer->load(['innovator.household', 'recordedBy', 'evidences']);

        return view('knowledge-transfers.show', ['transfer' => $knowledgeTransfer]);
    }
}
