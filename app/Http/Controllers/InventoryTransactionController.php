<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryLedgerException;
use App\Http\Requests\StoreInventoryTransactionRequest;
use App\Models\Household;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\AreaScopeService;
use App\Services\InventoryLedgerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Inventory ledger (Blueprint 8.3). index() here is the household-scoped
 * transaction history; manual movements (sale/transfer/loss/adjustment -
 * production and farm_use are always written by KilnBatchController/
 * ProductUsageController instead, see their doc-comments) are recorded
 * via create()/store(), household-locked like KilnBatchController.
 */
class InventoryTransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', InventoryTransaction::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $transactions = InventoryTransaction::with(['product', 'household', 'buyer', 'recordedBy'])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30);

        return view('inventory-transactions.index', compact('transactions'));
    }

    public function create(Request $request, InventoryLedgerService $ledger)
    {
        $household = Household::find($request->integer('household_id'));

        if (! $household) {
            return redirect()
                ->route('households.index')
                ->with('status', 'กรุณาเปิดหน้าครัวเรือนที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกการเคลื่อนไหวสต็อก" จากหน้านั้น');
        }

        $this->authorize('create', [InventoryTransaction::class, $household]);

        $products = Product::orderBy('name')->get()->map(fn ($product) => [
            'product' => $product,
            'balance' => $ledger->balance($product->id, $household->id),
        ]);

        return view('inventory-transactions.create', [
            'household' => $household,
            'products' => $products,
            'canAdjust' => $request->user()->hasAnyRole(\App\Models\Role::STAFF),
        ]);
    }

    public function store(StoreInventoryTransactionRequest $request, InventoryLedgerService $ledger)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [InventoryTransaction::class, $household]);

        $product = Product::findOrFail($request->input('product_id'));
        $data = $request->validated();

        try {
            $transaction = $ledger->record(
                product: $product,
                householdId: $household->id,
                type: $data['transaction_type'],
                quantity: (float) $data['quantity'],
                transactionDate: $data['transaction_date'],
                recordedBy: $request->user(),
                notes: $data['notes'] ?? null,
            );
        } catch (InventoryLedgerException $e) {
            return back()->withInput()->with('status', 'บันทึกไม่สำเร็จ: '.$e->getMessage());
        }

        return redirect()->route('households.show', $household)->with('status', 'บันทึกการเคลื่อนไหวสต็อกเรียบร้อย ('.$transaction->transaction_type.')');
    }
}
