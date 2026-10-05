<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCropSeasonRequest;
use App\Http\Requests\UpdateCropSeasonRequest;
use App\Models\CropSeason;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * ฤดูผลิต (Crop Season) master data management - previously had zero CRUD
 * UI anywhere in the system (model/migration/seeder only - see
 * DRFIS-Workflow-Menu-Analysis-23Sep.md ข้อ 3/4). Every other module just
 * reads `CropSeason::orderByDesc('start_date')` for its dropdown filter or
 * `CropSeason::where('status', 'active')->first()` assuming there is at
 * most one active season at a time - that assumption was never enforced
 * anywhere in code until now, so store()/update() enforce it here: saving
 * a season as "active" automatically closes any other season currently
 * marked active, instead of leaving multiple "active" rows for those
 * queries to silently pick the wrong one from.
 *
 * See CropSeasonPolicy's doc-comment for why create/update is Super
 * Admin/Project Admin only rather than any STAFF role.
 */
class CropSeasonController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', CropSeason::class);

        $seasons = CropSeason::orderByDesc('start_date')->paginate(20);

        return view('crop-seasons.index', compact('seasons'));
    }

    public function create()
    {
        $this->authorize('create', CropSeason::class);

        return view('crop-seasons.create');
    }

    public function store(StoreCropSeasonRequest $request)
    {
        $this->authorize('create', CropSeason::class);

        $data = $request->validated();
        $this->closeOtherActiveSeasons($data);

        $season = CropSeason::create($data);

        return redirect()->route('crop-seasons.index', $season)->with('status', 'เพิ่มฤดูผลิตเรียบร้อยแล้ว');
    }

    public function edit(CropSeason $cropSeason)
    {
        $this->authorize('update', CropSeason::class);

        return view('crop-seasons.edit', ['season' => $cropSeason]);
    }

    public function update(UpdateCropSeasonRequest $request, CropSeason $cropSeason)
    {
        $this->authorize('update', CropSeason::class);

        $data = $request->validated();
        $this->closeOtherActiveSeasons($data, $cropSeason);

        $cropSeason->update($data);

        return redirect()->route('crop-seasons.index')->with('status', 'บันทึกฤดูผลิตเรียบร้อยแล้ว');
    }

    /**
     * Enforce "at most one active season" - see this class's doc-comment.
     * Runs before the actual create/update so the row being saved always
     * wins the "active" slot if it asks for it.
     */
    private function closeOtherActiveSeasons(array $data, ?CropSeason $except = null): void
    {
        if (($data['status'] ?? null) !== 'active') {
            return;
        }

        CropSeason::where('status', 'active')
            ->when($except, fn ($q) => $q->whereKeyNot($except->id))
            ->update(['status' => 'closed']);
    }
}
