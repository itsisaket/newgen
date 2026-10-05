<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseholdRequest;
use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\FarmerGroup;
use App\Models\Farm;
use App\Models\Household;
use App\Models\Province;
use App\Services\AreaScopeService;
use App\Support\CodeGenerator;
use App\Support\FarmerAccountProvisioner;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * Household registration (Blueprint section 3.1/7.1 - Household is the top
 * of the Household -> Farm -> Plot hierarchy). This was missing from
 * Sprint 1/2: F01 and F13 can only ever pick a household/plot that already
 * exists via a dropdown - there was no screen to register a new one. This
 * is master/reference data, so plain CRUD (no Draft/Submitted/Verified/
 * Approved workflow, no WorkflowService) - `status` just tracks whether
 * the household is still an active participant.
 *
 * Authorization (15 ก.ย. round): every action here now goes through
 * HouseholdPolicy, which in turn checks AreaScopeService - see that
 * policy's doc-comment for why (previously any logged-in user, Farmer
 * logins included, could see/edit every household in the system).
 */
class HouseholdController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', Household::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());
        $search = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), ['active', 'inactive', 'withdrawn'], true)
            ? $request->query('status')
            : null;

        $scopedHouseholds = Household::query()
            ->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds));

        $summary = [
            'households' => (clone $scopedHouseholds)->count(),
            'active' => (clone $scopedHouseholds)->where('status', 'active')->count(),
            'farms' => Farm::query()
                ->when($householdIds !== null, fn ($q) => $q->whereIn('household_id', $householdIds))
                ->count(),
            'groups' => (clone $scopedHouseholds)
                ->whereNotNull('farmer_group_id')
                ->distinct()
                ->count('farmer_group_id'),
        ];

        // 'user.roles' (not just 'user') so the index page's "นวัตกร" badge
        // (->isInnovator() -> Spatie's hasRole()) doesn't run an extra
        // roles query per row.
        $households = Household::with([
            'farmerGroup.tambon.district.province',
            'village.tambon.district.province',
            'user.roles',
        ])
            ->withCount('farms')
            ->withCount(['farms as plots_count' => fn ($q) => $q
                ->join('plots', 'plots.farm_id', '=', 'farms.id')])
            ->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds))
            ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                ->where('head_name', 'like', '%'.$search.'%')
                ->orWhere('household_code', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')))
            ->when($request->filled('province_id'), fn ($q) => $q->where(fn ($location) => $location
                ->whereHas('farmerGroup.tambon.district', fn ($district) => $district->where('province_id', $request->integer('province_id')))
                ->orWhereHas('village.tambon.district', fn ($district) => $district->where('province_id', $request->integer('province_id')))))
            ->when($request->filled('district_id'), fn ($q) => $q->where(fn ($location) => $location
                ->whereHas('farmerGroup.tambon', fn ($tambon) => $tambon->where('district_id', $request->integer('district_id')))
                ->orWhereHas('village.tambon', fn ($tambon) => $tambon->where('district_id', $request->integer('district_id')))))
            ->when($request->filled('tambon_id'), fn ($q) => $q->where(fn ($location) => $location
                ->whereHas('farmerGroup', fn ($group) => $group->where('tambon_id', $request->integer('tambon_id')))
                ->orWhereHas('village', fn ($village) => $village->where('tambon_id', $request->integer('tambon_id')))))
            ->when($request->filled('farmer_group_id'), fn ($q) => $q->where('farmer_group_id', $request->integer('farmer_group_id')))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('household_code')
            ->paginate(20)
            ->withQueryString();

        return view('households.index', [
            'households' => $households,
            'summary' => $summary,
            'provinces' => Province::orderBy('name_th')->get(),
            'farmerGroups' => FarmerGroup::orderBy('name')->get(['id', 'name']),
            'selectedProvinceId' => $request->integer('province_id') ?: config('drfis.default_province_id'),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Household::class);

        return view('households.create', array_merge($this->locationOptions(), [
            'farmerGroups' => FarmerGroup::where('is_active', true)->orderBy('name')->get(),
        ]));
    }

    public function store(StoreHouseholdRequest $request)
    {
        $this->authorize('create', Household::class);

        $data = $request->validated();
        $data['id_card_number_encrypted'] = $data['id_card_number'] ?? null;
        unset($data['id_card_number']);
        // head_name is the primary display name now - household_code is
        // just an internal reference id, assigned automatically instead of
        // typed by hand (see StoreHouseholdRequest).
        $data['household_code'] = CodeGenerator::next(Household::class, 'household_code', 'HH');

        $household = Household::create($data);

        // 1 ครัวเรือน = 1 เกษตรกร (login Farmer) - every household gets its
        // own Farmer-role account the moment it's registered, not as a
        // separate manual step. The plaintext password is only available
        // right here - flash it once so the next page can show it, then
        // it's gone for good (see App\Support\FarmerAccountProvisioner).
        $credentials = FarmerAccountProvisioner::provision($household);

        // Auto-chaining registration flow (สมัครสมาชิก -> เพิ่มครัวเรือน ->
        // เพิ่มสวน -> เพิ่มแปลง -> เครื่องมือวิจัย): after saving a household,
        // go straight into "add farm" with this household pre-selected,
        // instead of stopping on the household's show page. This is the
        // default behaviour, not an opt-in wizard mode.
        return redirect()
            ->route('farms.create', ['household_id' => $household->id])
            ->with('status', 'ลงทะเบียนครัวเรือนเรียบร้อยแล้ว ขั้นตอนต่อไป: เพิ่มสวน')
            ->with('farmer_credentials', $credentials);
    }

    public function show(Household $household)
    {
        $this->authorize('view', $household);

        $household->load([
            'farmerGroup.tambon.district.province', 'village', 'farms.plots', 'user.roles', 'innovatorEvaluations',
            // Sprint 3: Technology & Biomass round.
            'technologyAssignments' => fn ($q) => $q->where('status', 'active')->with('technologyAsset.technology'),
            'kilnBatches' => fn ($q) => $q->whereNull('original_record_id')->with('technologyAsset')->limit(5),
            // Sprint 4 round: F07 ALP history + F08 Sales history cards.
            'alpAssessments' => fn ($q) => $q->with('technology')->limit(10),
            'sales' => fn ($q) => $q->with(['buyer', 'product'])->limit(10),
            // Sprint 5 round: F15 Carbon Activity Monitoring card.
            'carbonActivities' => fn ($q) => $q->whereNull('original_record_id')->with('calculation')->limit(5),
        ]);

        // Current stock balance per product this household has ever moved
        // (Blueprint 8.3) - same "latest transaction = current balance"
        // lookup as ProductController::show(), just scoped to one
        // household instead of one product.
        $productBalances = \App\Models\InventoryTransaction::query()
            ->where('household_id', $household->id)
            ->selectRaw('product_id, MAX(id) as last_id')
            ->groupBy('product_id')
            ->pluck('last_id')
            ->map(fn ($id) => \App\Models\InventoryTransaction::with('product')->find($id))
            ->filter();

        return view('households.show', compact('household', 'productBalances'));
    }

    public function edit(Household $household)
    {
        $this->authorize('update', $household);

        // The จังหวัด/อำเภอ/ตำบล selects on this form are pure UI filters
        // (household's real location comes transitively through
        // farmer_group/village, not its own columns) - derive their
        // initial values from whichever of the two is already set, so the
        // cascade shows the right context instead of resetting to blank.
        $household->loadMissing(['farmerGroup.tambon.district', 'village.tambon.district']);
        $tambon = $household->farmerGroup?->tambon ?? $household->village?->tambon;

        return view('households.edit', array_merge($this->locationOptions(), [
            'household' => $household,
            'farmerGroups' => FarmerGroup::where('is_active', true)->orderBy('name')->get(),
            'selectedProvinceId' => $tambon?->district?->province_id,
            'selectedDistrictId' => $tambon?->district_id,
            'selectedTambonId' => $tambon?->id,
        ]));
    }

    public function update(UpdateHouseholdRequest $request, Household $household)
    {
        $this->authorize('update', $household);

        $data = $request->validated();
        $data['id_card_number_encrypted'] = $data['id_card_number'] ?? null;
        unset($data['id_card_number']);

        $household->update($data);

        // Keep the linked Farmer login's name/phone in sync with the
        // household record. The login's own status is deliberately NOT
        // tied to household->status - user confirmed (15 ก.ย.) that a
        // household going inactive/withdrawn should not lock its Farmer
        // account out (e.g. a นวัตกรชุมชน keeps their login even after
        // "graduating"). Suspending a Farmer login, if ever needed, is a
        // separate explicit action, not an automatic side effect of
        // editing the household. A household saved before this feature
        // existed (no linked account yet) is left alone until
        // `php artisan db:seed` backfills one.
        if ($household->user) {
            $household->user->forceFill([
                'name' => $household->head_name,
                'phone' => $household->phone,
            ])->save();
        }

        return redirect()
            ->route('households.show', $household)
            ->with('status', 'บันทึกข้อมูลครัวเรือนเรียบร้อยแล้ว');
    }

    /**
     * Reset the household's Farmer login password (e.g. field staff lost
     * the one-time password shown at registration). Shows the new
     * password once, the same way registration does.
     */
    public function resetFarmerPassword(Household $household)
    {
        $this->authorize('update', $household);

        $credentials = FarmerAccountProvisioner::resetPassword($household);

        if (! $credentials) {
            return back()->with('status', 'ครัวเรือนนี้ยังไม่มีบัญชีเกษตรกร - รัน `php artisan db:seed` เพื่อสร้างให้อัตโนมัติ');
        }

        return back()
            ->with('status', 'รีเซ็ตรหัสผ่านบัญชีเกษตรกรเรียบร้อยแล้ว')
            ->with('farmer_credentials', $credentials);
    }

    /**
     * Only the province list is loaded up front (small - 77 rows).
     * District/tambon options are fetched on demand via routes
     * locations.districts / locations.tambons
     * (public/js/cascading-location.js) - see
     * FarmController::locationOptions() for why, now that LocationSeeder
     * installs the full official dataset instead of the old pilot subset.
     *
     * `defaultProvinceId` (config/drfis.php - ศรีสะเกษ) pre-selects the
     * province filter <select> on the create form, since this system only
     * ever operates in that one province - see config/drfis.php doc-comment.
     */
    private function locationOptions(): array
    {
        return [
            'provinces' => Province::orderBy('name_th')->get(['id', 'name_th']),
            'defaultProvinceId' => config('drfis.default_province_id'),
        ];
    }
}
