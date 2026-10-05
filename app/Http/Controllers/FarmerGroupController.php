<?php

namespace App\Http\Controllers;

use App\Models\FarmerGroup;
use App\Models\Province;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FarmerGroupController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', FarmerGroup::class);

        $groups = FarmerGroup::query()
            ->with('tambon.district.province')
            ->withCount('households')
            ->when($request->filled('province_id'), fn ($q) => $q->whereHas('tambon.district', fn ($d) => $d->where('province_id', $request->integer('province_id'))))
            ->when($request->filled('district_id'), fn ($q) => $q->whereHas('tambon', fn ($t) => $t->where('district_id', $request->integer('district_id'))))
            ->when($request->filled('tambon_id'), fn ($q) => $q->where('tambon_id', $request->integer('tambon_id')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$request->string('q')->trim().'%')
                ->orWhere('leader_name', 'like', '%'.$request->string('q')->trim().'%')))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('farmer-groups.index', [
            'groups' => $groups,
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', FarmerGroup::class);

        return view('farmer-groups.form', [
            'group' => new FarmerGroup(['tambon_id' => $request->integer('tambon_id') ?: null]),
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', FarmerGroup::class);
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active', true);
        FarmerGroup::create($data);

        return redirect()->route('farmer-groups.index', ['tambon_id' => $data['tambon_id']])
            ->with('status', 'เพิ่มกลุ่มเกษตรกรเรียบร้อยแล้ว');
    }

    public function edit(FarmerGroup $farmerGroup)
    {
        $this->authorize('update', $farmerGroup);
        $farmerGroup->load('tambon.district');

        return view('farmer-groups.form', [
            'group' => $farmerGroup,
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function update(Request $request, FarmerGroup $farmerGroup)
    {
        $this->authorize('update', $farmerGroup);
        $data = $this->validated($request, $farmerGroup);
        $data['is_active'] = $request->boolean('is_active');
        $farmerGroup->update($data);

        return redirect()->route('farmer-groups.index', ['tambon_id' => $data['tambon_id']])
            ->with('status', 'แก้ไขกลุ่มเกษตรกรเรียบร้อยแล้ว');
    }

    private function validated(Request $request, ?FarmerGroup $group = null): array
    {
        return $request->validate([
            'tambon_id' => ['required', 'exists:tambons,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('farmer_groups')->where(fn ($q) => $q->where('tambon_id', $request->integer('tambon_id')))->ignore($group)],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
