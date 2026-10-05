<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFarmRequest;
use App\Http\Requests\UpdateFarmRequest;
use App\Models\Farm;
use App\Models\Household;
use App\Models\Province;
use App\Services\AreaScopeService;
use App\Support\CodeGenerator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Farm registration (Blueprint section 7.1). Master/reference data - see
 * HouseholdController for the same reasoning (no workflow here).
 *
 * Authorization (15 ก.ย. round): see FarmPolicy/HouseholdPolicy.
 *
 * Sequential-entry hardening (15 ก.ย. round 2): ผู้ใช้ขอให้ลำดับการใช้งาน
 * บังคับเป็น ครัวเรือน -> สวน -> แปลง -> กิจกรรมสวน เท่านั้น ห้ามเข้าจากจุดอื่น
 * และ "ป้องกันการเปลี่ยนยิ่งดี" (ห้ามย้าย parent ทีหลังด้วย) - create() จึงไม่มี
 * dropdown ให้เลือกครัวเรือนอิสระอีกต่อไป ต้องมาจาก household_id ที่ส่งมาจริง
 * เท่านั้น (ลิงก์ "+ เพิ่มสวน" บนหน้า households/show.blade.php เพียงจุดเดียว) และ
 * update() ไม่รับ household_id จากฟอร์มอีกต่อไปเลย (ดู UpdateFarmRequest) - สวนที่
 * สร้างแล้วจึงย้ายข้ามครัวเรือนไม่ได้อีกต่อไป ไม่ว่ากรณีใด
 */
class FarmController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', Farm::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $farms = Farm::with('household')
            ->withCount('plots')
            ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
            ->orderBy('farm_code')
            ->paginate(20);

        return view('farms.index', compact('farms'));
    }

    /**
     * No free household dropdown here on purpose - a farm may only be
     * created starting from a specific household's page (households/show
     * -> "+ เพิ่มสวน", which passes ?household_id=). Arriving without a
     * valid household_id (bare /farms/create, an old bookmark, a stray
     * sidenav link) bounces back to the household list with guidance
     * instead of showing a form with no context.
     */
    public function create(Request $request)
    {
        $household = Household::find($request->integer('household_id'));

        if (! $household) {
            return redirect()
                ->route('households.index')
                ->with('status', 'กรุณาเปิดหน้าครัวเรือนที่ต้องการก่อน แล้วกดปุ่ม "+ เพิ่มสวน" จากหน้านั้น (เพิ่มสวนแบบแยกอิสระไม่ได้แล้ว เพื่อป้องกันความสับสนของลำดับ ครัวเรือน → สวน → แปลง)');
        }

        $this->authorize('create', [Farm::class, $household]);

        return view('farms.create', array_merge($this->locationOptions(), [
            'household' => $household,
        ]));
    }

    public function store(StoreFarmRequest $request)
    {
        $household = Household::findOrFail($request->input('household_id'));
        $this->authorize('create', [Farm::class, $household]);

        // farm_name is the primary display name now - farm_code is just an
        // internal reference id, assigned automatically instead of typed
        // by hand (see StoreFarmRequest).
        $data = $request->validated();
        $data['farm_code'] = CodeGenerator::next(Farm::class, 'farm_code', 'FARM');

        $farm = Farm::create($data);

        // Auto-chaining registration flow - see HouseholdController::store().
        // After saving a farm, go straight into "add plot" with this farm
        // pre-selected.
        return redirect()
            ->route('plots.create', ['farm_id' => $farm->id])
            ->with('status', 'เพิ่มสวนเรียบร้อยแล้ว ขั้นตอนต่อไป: เพิ่มแปลง');
    }

    public function show(Farm $farm)
    {
        $this->authorize('view', $farm);

        $farm->load(['household', 'plots.durianVariety', 'province', 'district', 'tambon']);

        return view('farms.show', compact('farm'));
    }

    public function edit(Farm $farm)
    {
        $this->authorize('update', $farm);

        $farm->load('household');

        return view('farms.edit', array_merge($this->locationOptions(), [
            'farm' => $farm,
        ]));
    }

    /**
     * household_id is no longer accepted here at all (see
     * UpdateFarmRequest - it has no rule for that key, so
     * $request->validated() never contains it even if someone crafts a
     * request with it) - a farm can never be moved to a different
     * household after creation, so there is nothing left to re-check
     * against the target household the way this used to.
     */
    public function update(UpdateFarmRequest $request, Farm $farm)
    {
        $this->authorize('update', $farm);

        $farm->update($request->validated());

        return redirect()
            ->route('farms.show', $farm)
            ->with('status', 'บันทึกข้อมูลสวนเรียบร้อยแล้ว');
    }

    /**
     * Only the province list is loaded up front (small - 77 rows).
     * District/tambon options are fetched on demand via
     * routes locations.districts / locations.tambons
     * (public/js/cascading-location.js) now that LocationSeeder installs
     * the full official dataset (930 districts / 7,452 tambons) - too
     * much to inline into every page load, unlike the old 3-province
     * pilot subset this used to serve whole.
     *
     * `defaultProvinceId` (config/drfis.php - ศรีสะเกษ) pre-selects the
     * province <select> on the create form, since this system only ever
     * operates in that one province - see config/drfis.php doc-comment.
     */
    private function locationOptions(): array
    {
        return [
            'provinces' => Province::orderBy('name_th')->get(['id', 'name_th']),
            'defaultProvinceId' => config('drfis.default_province_id'),
        ];
    }
}
