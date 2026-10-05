<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuyerRequest;
use App\Models\Buyer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * F08 full - buyers master data (Blueprint Appendix C.5). Previously this
 * table (F14/F08-lite round) had no dedicated management screen at all -
 * harvest-records.create just read from whatever rows existed via
 * `Buyer::orderBy('name')->get()`, with no way to add one through the UI.
 * This closes that gap the same way TechnologyController does for F12:
 * a simple master-data index/create/show, no edit/destroy.
 */
class BuyerController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Buyer::class);

        $buyers = Buyer::withCount(['sales', 'marketValidations'])->orderBy('name')->paginate(20);

        return view('buyers.index', compact('buyers'));
    }

    public function create()
    {
        $this->authorize('create', Buyer::class);

        return view('buyers.create');
    }

    public function store(StoreBuyerRequest $request)
    {
        $this->authorize('create', Buyer::class);

        $buyer = Buyer::create($request->validated());

        return redirect()->route('buyers.show', $buyer)->with('status', 'เพิ่มผู้ซื้อเรียบร้อยแล้ว');
    }

    public function show(Buyer $buyer)
    {
        $this->authorize('view', $buyer);

        $buyer->load([
            'marketValidations' => fn ($q) => $q->latest('valid_from'),
            'sales' => fn ($q) => $q->with('sellerHousehold')->latest('sale_date')->limit(10),
        ]);

        return view('buyers.show', compact('buyer'));
    }
}
