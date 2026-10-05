<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryLedgerException;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Buyer;
use App\Models\CropSeason;
use App\Models\Household;
use App\Models\Product;
use App\Models\Sale;
use App\Services\AreaScopeService;
use App\Services\InventoryLedgerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * F08 full - actual sales (Blueprint ค.5 `sales` - the revenue source for
 * F06's EconomicImpactService). Household-locked create like KilnBatch.
 *
 * total_amount is ALWAYS computed here as quantity * unit_price, never
 * accepted from the client (see StoreSaleRequest). For a bioproduct sale,
 * store() also calls InventoryLedgerService so this is the ONE place both
 * the revenue record AND the stock deduction happen together, atomically
 * - never two separate steps a user could do out of order or only
 * half-complete (see the sales migration's doc-comment for why this
 * supersedes the generic InventoryTransactionController 'sale' type).
 */
class SaleController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', Sale::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $sales = Sale::with(['sellerHousehold', 'buyer', 'product'])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('seller_household_id', $householdIds))
            ->latest('sale_date')
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function create(Request $request, InventoryLedgerService $ledger)
    {
        $household = Household::find($request->integer('household_id'));

        if (! $household) {
            return redirect()
                ->route('households.index')
                ->with('status', 'กรุณาเปิดหน้าครัวเรือนที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกการขาย" จากหน้านั้น');
        }

        $this->authorize('create', [Sale::class, $household]);

        return view('sales.create', [
            'household' => $household,
            'buyers' => Buyer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get()->map(fn ($product) => [
                'product' => $product,
                'balance' => $ledger->balance($product->id, $household->id),
            ]),
            'cropSeasons' => CropSeason::orderByDesc('start_date')->get(),
        ]);
    }

    public function store(StoreSaleRequest $request, InventoryLedgerService $ledger)
    {
        $household = Household::findOrFail($request->input('seller_household_id'));
        $this->authorize('create', [Sale::class, $household]);

        $data = $request->validated();
        $data['total_amount'] = round((float) $data['quantity'] * (float) $data['unit_price'], 2);
        $data['recorded_by'] = $request->user()->id;

        try {
            $sale = DB::transaction(function () use ($data, $household, $request, $ledger) {
                $sale = Sale::create($data);

                if ($sale->product_type === 'bioproduct') {
                    $ledger->record(
                        product: Product::findOrFail($sale->product_id),
                        householdId: $household->id,
                        type: 'sale',
                        quantity: (float) $sale->quantity,
                        transactionDate: $sale->sale_date,
                        recordedBy: $request->user(),
                        reference: $sale,
                        buyerId: $sale->buyer_id,
                        notes: $sale->notes,
                    );
                }

                return $sale;
            });
        } catch (InventoryLedgerException $e) {
            return back()->withInput()->with('status', 'บันทึกการขายไม่สำเร็จ: '.$e->getMessage());
        }

        return redirect()->route('sales.show', $sale)->with('status', 'บันทึกการขายเรียบร้อยแล้ว');
    }

    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load(['sellerHousehold', 'buyer', 'product', 'cropSeason', 'recordedBy']);

        return view('sales.show', compact('sale'));
    }
}
