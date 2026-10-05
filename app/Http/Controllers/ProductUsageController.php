<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryLedgerException;
use App\Http\Requests\StoreProductUsageRequest;
use App\Models\Plot;
use App\Models\Product;
use App\Models\ProductUsage;
use App\Services\AreaScopeService;
use App\Services\InventoryLedgerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * F04 - Bio-product Utilization (Blueprint section 7.2's "Biochar/น้ำส้ม
 * ควันไม้" activity row / Appendix C.4 `product_usages`). Plot-locked
 * create like F13/F08-harvest. Unlike KilnBatch, this writes its
 * inventory_transactions row (type farm_use) immediately at store() -
 * there's no separate approval step for "we used some of our own stock
 * on our own plot" the way there is for "we're crediting new stock to
 * the ledger" (production). InventoryLedgerService still enforces that
 * the household actually HAS enough of the product first.
 */
class ProductUsageController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', ProductUsage::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $usages = ProductUsage::with(['plot.farm.household', 'inventoryTransaction.product'])
            ->when($householdIds !== null, fn ($q) => $q->whereHas('plot.farm', fn ($f) => $f->whereIn('household_id', $householdIds)))
            ->latest('application_date')
            ->paginate(20);

        return view('product-usages.index', compact('usages'));
    }

    public function create(Request $request)
    {
        $plot = Plot::with('farm.household')->find($request->integer('plot_id'));

        if (! $plot) {
            return redirect()
                ->route('plots.index')
                ->with('status', 'กรุณาเปิดหน้าแปลงที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกการใช้ผลิตภัณฑ์" จากหน้านั้น');
        }

        $this->authorize('create', [ProductUsage::class, $plot]);

        $householdId = $plot->farm->household_id;
        $ledger = app(InventoryLedgerService::class);

        return view('product-usages.create', [
            'plot' => $plot,
            'products' => Product::orderBy('name')->get()->map(fn ($product) => [
                'product' => $product,
                'balance' => $ledger->balance($product->id, $householdId),
            ]),
        ]);
    }

    public function store(StoreProductUsageRequest $request, InventoryLedgerService $ledger)
    {
        $plot = Plot::with('farm')->findOrFail($request->input('plot_id'));
        $this->authorize('create', [ProductUsage::class, $plot]);

        $product = Product::findOrFail($request->input('product_id'));
        $data = $request->validated();

        try {
            $usage = DB::transaction(function () use ($ledger, $product, $plot, $data, $request) {
                $transaction = $ledger->record(
                    product: $product,
                    householdId: $plot->farm->household_id,
                    type: 'farm_use',
                    quantity: (float) $data['quantity'],
                    transactionDate: $data['application_date'],
                    recordedBy: $request->user(),
                );

                return ProductUsage::create([
                    'inventory_transaction_id' => $transaction->id,
                    'plot_id' => $plot->id,
                    'usage_rate' => $data['usage_rate'] ?? null,
                    'application_method' => $data['application_method'] ?? null,
                    'application_date' => $data['application_date'],
                ]);
            });
        } catch (InventoryLedgerException $e) {
            return back()->withInput()->with('status', 'บันทึกไม่สำเร็จ: '.$e->getMessage());
        }

        return redirect()->route('product-usages.show', $usage)->with('status', 'บันทึกการใช้ผลิตภัณฑ์เรียบร้อย');
    }

    public function show(ProductUsage $productUsage)
    {
        $this->authorize('view', $productUsage);

        $productUsage->load(['plot.farm.household', 'inventoryTransaction.product']);

        return view('product-usages.show', ['usage' => $productUsage]);
    }
}
