<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTechnologyAssetRequest;
use App\Models\Technology;
use App\Models\TechnologyAsset;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * F12 - physical asset registry under a technology type (Blueprint 8.1).
 * Registered from a technology's page (technologies/show -> "+ ลงทะเบียน
 * เครื่อง") - same "locked to parent context" convention as
 * Farm/Plot/FarmActivity, one level up (Technology -> TechnologyAsset).
 */
class TechnologyAssetController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', TechnologyAsset::class);

        $assets = TechnologyAsset::with(['technology', 'assignments' => fn ($q) => $q->where('status', 'active')->with('household')])
            ->orderBy('asset_code')
            ->paginate(20);

        return view('technology-assets.index', compact('assets'));
    }

    public function create(\Illuminate\Http\Request $request)
    {
        $technology = Technology::find($request->integer('technology_id'));

        if (! $technology) {
            return redirect()
                ->route('technologies.index')
                ->with('status', 'กรุณาเปิดหน้าประเภทเทคโนโลยีที่ต้องการก่อน แล้วกดปุ่ม "+ ลงทะเบียนเครื่อง" จากหน้านั้น');
        }

        $this->authorize('create', TechnologyAsset::class);

        return view('technology-assets.create', compact('technology'));
    }

    public function store(StoreTechnologyAssetRequest $request)
    {
        $this->authorize('create', TechnologyAsset::class);

        $asset = TechnologyAsset::create($request->validated());

        return redirect()->route('technologies.show', $asset->technology_id)->with('status', 'ลงทะเบียนเครื่องเรียบร้อยแล้ว');
    }

    public function show(TechnologyAsset $technologyAsset)
    {
        $this->authorize('view', $technologyAsset);

        $technologyAsset->load(['technology', 'assignments.household', 'assignments.assignedBy', 'kilnBatches' => fn ($q) => $q->latest('batch_date')->limit(10)]);

        return view('technology-assets.show', ['asset' => $technologyAsset]);
    }
}
