<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * F04/Inventory master data (product TYPES - Blueprint Appendix C.4
 * `products`). See ProductPolicy's doc-comment. show() doubles as a
 * cross-household stock overview for this product (Blueprint 8.3's
 * ledger, summed per household) rather than a bare master-data detail
 * page, since that is the more useful thing to land on from the sidenav.
 */
class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::orderBy('name')->paginate(20);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('products.create');
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = Product::create($request->validated());

        return redirect()->route('products.show', $product)->with('status', 'เพิ่มประเภทผลิตภัณฑ์เรียบร้อยแล้ว');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        // Latest transaction per household = current balance for that
        // household (InventoryLedgerService::balance() does the same
        // lookup for a single household - this is the cross-household
        // overview version for this product's page).
        $balances = InventoryTransaction::query()
            ->where('product_id', $product->id)
            ->selectRaw('household_id, MAX(id) as last_id')
            ->groupBy('household_id')
            ->pluck('last_id')
            ->map(fn ($id) => InventoryTransaction::with('household')->find($id))
            ->filter()
            ->sortByDesc(fn ($t) => $t->balance_after);

        return view('products.show', compact('product', 'balances'));
    }
}
