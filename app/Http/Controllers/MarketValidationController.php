<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarketValidationRequest;
use App\Models\Buyer;
use App\Models\MarketValidation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F08 full - Market Validation (Blueprint 10.3). Buyer-locked create -
 * same "reachable only from the parent's page" convention as
 * household-locked/plot-locked modules, just with Buyer as the parent
 * since a demand record only makes sense attached to one buyer.
 */
class MarketValidationController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', MarketValidation::class);

        $validations = MarketValidation::with('buyer')->latest('valid_from')->paginate(20);

        return view('market-validations.index', compact('validations'));
    }

    public function create(Request $request)
    {
        $buyer = Buyer::find($request->integer('buyer_id'));

        if (! $buyer) {
            return redirect()
                ->route('buyers.index')
                ->with('status', 'กรุณาเปิดหน้าผู้ซื้อที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกความต้องการตลาด" จากหน้านั้น');
        }

        $this->authorize('create', MarketValidation::class);

        return view('market-validations.create', compact('buyer'));
    }

    public function store(StoreMarketValidationRequest $request)
    {
        $this->authorize('create', MarketValidation::class);

        $data = $request->validated();
        $data['has_loi_mou'] = $request->boolean('has_loi_mou');
        $data['recorded_by'] = $request->user()->id;

        $validation = MarketValidation::create($data);

        return redirect()->route('market-validations.show', $validation)->with('status', 'บันทึกความต้องการตลาดเรียบร้อยแล้ว');
    }

    public function show(MarketValidation $marketValidation)
    {
        $this->authorize('view', $marketValidation);

        $marketValidation->load(['buyer', 'recordedBy']);

        return view('market-validations.show', ['validation' => $marketValidation]);
    }
}
