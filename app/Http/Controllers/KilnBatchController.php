<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKilnBatchRequest;
use App\Models\Household;
use App\Models\KilnBatch;
use App\Models\Product;
use App\Models\TechnologyAsset;
use App\Services\AreaScopeService;
use App\Services\InventoryLedgerService;
use App\Services\WorkflowService;
use App\Exceptions\InventoryLedgerException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * F03 - Kiln Production Log (Blueprint section 8.1). Household-locked
 * create like FarmActivityController is plot-locked (households/show ->
 * "+ บันทึกการเดินเตา").
 *
 * Inventory is only credited on APPROVE, not at creation: a kiln_batches
 * row goes through the same Draft -> Submitted -> Verified -> Approved
 * workflow as F13/F08-harvest (see the migration's doc-comment), and
 * writing production inventory_transactions for every output the moment
 * a Draft is saved would let an unverified number silently become real
 * stock a household could then try to sell/use before anyone checked it.
 * approve() is therefore the one place besides
 * ProductUsageController/InventoryTransactionController that calls
 * InventoryLedgerService.
 */
class KilnBatchController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', KilnBatch::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $batches = KilnBatch::with(['household', 'technologyAsset.technology', 'operator'])
            ->whereNull('original_record_id')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->latest('batch_date')
            ->paginate(20);

        return view('kiln-batches.index', compact('batches'));
    }

    public function create(Request $request)
    {
        $household = Household::find($request->integer('household_id'));

        if (! $household) {
            return redirect()
                ->route('households.index')
                ->with('status', 'กรุณาเปิดหน้าครัวเรือนที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกการเดินเตา" จากหน้านั้น');
        }

        $this->authorize('create', [KilnBatch::class, $household]);

        return view('kiln-batches.create', [
            'household' => $household,
            // Only assets currently assigned to this household - a kiln
            // batch can't be logged against equipment the household
            // doesn't have (Blueprint 8.1's allocation history is what
            // TechnologyAssignment tracks).
            'assets' => TechnologyAsset::whereHas('assignments', fn ($q) => $q->where('household_id', $household->id)->where('status', 'active'))
                ->with('technology')
                ->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(StoreKilnBatchRequest $request)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [KilnBatch::class, $household]);

        $data = $request->validated();
        $outputs = $data['outputs'];
        unset($data['outputs']);

        $data['operator_id'] = $data['operator_id'] ?? $request->user()->id;
        $data['recorded_by'] = $request->user()->id;

        $batch = DB::transaction(function () use ($data, $outputs) {
            $batch = KilnBatch::create($data);

            foreach ($outputs as $output) {
                $batch->outputs()->create($output);
            }

            return $batch;
        });

        return redirect()->route('kiln-batches.show', $batch)->with('status', 'บันทึกการเดินเตาเรียบร้อย (สถานะ: ร่าง)');
    }

    public function show(KilnBatch $kilnBatch)
    {
        $this->authorize('view', $kilnBatch);

        $kilnBatch->load(['household', 'technologyAsset.technology', 'operator', 'recordedBy', 'outputs.product', 'evidences']);

        return view('kiln-batches.show', ['batch' => $kilnBatch]);
    }

    public function submit(KilnBatch $kilnBatch, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $kilnBatch);

        $workflow->submit($kilnBatch, $request->user());

        return back()->with('status', 'ส่งข้อมูลเพื่อตรวจสอบแล้ว');
    }

    public function verify(KilnBatch $kilnBatch, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $kilnBatch);

        $workflow->verify($kilnBatch, $request->user());

        return back()->with('status', 'ตรวจสอบข้อมูลแล้ว รอการอนุมัติ');
    }

    public function approve(KilnBatch $kilnBatch, Request $request, WorkflowService $workflow, InventoryLedgerService $ledger)
    {
        $this->authorize('view', $kilnBatch);

        try {
            DB::transaction(function () use ($kilnBatch, $request, $workflow, $ledger) {
                $workflow->approve($kilnBatch, $request->user());

                $kilnBatch->load('outputs.product');

                foreach ($kilnBatch->outputs as $output) {
                    $ledger->record(
                        product: $output->product,
                        householdId: $kilnBatch->household_id,
                        type: 'production',
                        quantity: (float) $output->output_quantity,
                        transactionDate: $kilnBatch->batch_date,
                        recordedBy: $request->user(),
                        reference: $kilnBatch,
                    );
                }
            });
        } catch (InventoryLedgerException $e) {
            return back()->with('status', 'อนุมัติไม่สำเร็จ: '.$e->getMessage());
        }

        return back()->with('status', 'อนุมัติข้อมูลแล้ว และบันทึกผลผลิตเข้าสต็อกเรียบร้อย');
    }

    public function reject(KilnBatch $kilnBatch, Request $request, WorkflowService $workflow)
    {
        $this->authorize('view', $kilnBatch);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $workflow->reject($kilnBatch, $request->user(), $request->string('rejection_reason'));

        return back()->with('status', 'ตีกลับข้อมูลแล้ว');
    }
}
