<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTechnologyAssignmentRequest;
use App\Models\Household;
use App\Models\TechnologyAsset;
use App\Models\TechnologyAssignment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F12 - allocate a technology_asset to a household (Blueprint 8.1). Locked
 * to a specific asset's page (technology-assets/show -> "+ จัดสรรให้ครัวเรือน") -
 * same convention as Farm/Plot/FarmActivity.
 *
 * "An asset must be returned before it can be reassigned": enforced here
 * in store() by checking TechnologyAsset::currentAssignment() rather than
 * a DB constraint (see the migration's doc-comment) - store() rejects a
 * new assignment for an asset that already has one with status=active.
 */
class TechnologyAssignmentController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request)
    {
        $asset = TechnologyAsset::with('technology', 'assignments')->find($request->integer('technology_asset_id'));

        if (! $asset) {
            return redirect()
                ->route('technologies.index')
                ->with('status', 'กรุณาเปิดหน้าเครื่องที่ต้องการก่อน แล้วกดปุ่ม "+ จัดสรรให้ครัวเรือน" จากหน้านั้น');
        }

        $this->authorize('create', TechnologyAssignment::class);

        if ($asset->currentAssignment()) {
            return redirect()
                ->route('technology-assets.show', $asset)
                ->with('status', 'เครื่องนี้ถูกจัดสรรให้ครัวเรือนอื่นอยู่แล้ว กรุณา "รับคืนเครื่อง" ก่อนจัดสรรให้ครัวเรือนใหม่');
        }

        return view('technology-assignments.create', [
            'asset' => $asset,
            'households' => Household::where('status', 'active')->orderBy('household_code')->get(['id', 'household_code', 'head_name']),
        ]);
    }

    public function store(StoreTechnologyAssignmentRequest $request)
    {
        $asset = TechnologyAsset::findOrFail($request->integer('technology_asset_id'));
        $household = Household::findOrFail($request->input('household_id'));

        $this->authorize('create', [TechnologyAssignment::class, $household]);

        if ($asset->currentAssignment()) {
            return back()->withInput()->with('status', 'เครื่องนี้ถูกจัดสรรให้ครัวเรือนอื่นอยู่แล้ว');
        }

        $data = $request->validated();
        $data['technology_asset_id'] = $asset->id;
        $data['assigned_by'] = $request->user()->id;
        $data['status'] = 'active';

        TechnologyAssignment::create($data);

        return redirect()->route('technology-assets.show', $asset)->with('status', 'จัดสรรเครื่องให้ครัวเรือนเรียบร้อยแล้ว');
    }

    public function returnAsset(TechnologyAssignment $technologyAssignment, Request $request)
    {
        $this->authorize('view', $technologyAssignment);

        if ($technologyAssignment->status !== 'active') {
            return back()->with('status', 'รายการนี้ถูกรับคืนไปแล้ว');
        }

        $technologyAssignment->update([
            'status' => 'returned',
            'returned_date' => now()->toDateString(),
        ]);

        return redirect()
            ->route('technology-assets.show', $technologyAssignment->technology_asset_id)
            ->with('status', 'รับคืนเครื่องเรียบร้อยแล้ว');
    }
}
