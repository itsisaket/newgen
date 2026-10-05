<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Village;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VillageController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Village::class);

        $villages = Village::query()
            ->with('tambon.district.province')
            ->when($request->filled('province_id'), fn ($q) => $q->whereHas('tambon.district', fn ($d) => $d->where('province_id', $request->integer('province_id'))))
            ->when($request->filled('district_id'), fn ($q) => $q->whereHas('tambon', fn ($t) => $t->where('district_id', $request->integer('district_id'))))
            ->when($request->filled('tambon_id'), fn ($q) => $q->where('tambon_id', $request->integer('tambon_id')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name_th', 'like', '%'.$request->string('q')->trim().'%')
                ->orWhere('official_code', 'like', '%'.$request->string('q')->trim().'%')))
            ->orderBy('tambon_id')
            ->orderBy('village_no')
            ->paginate(30)
            ->withQueryString();

        return view('villages.index', [
            'villages' => $villages,
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Village::class);

        return view('villages.form', [
            'village' => new Village(['tambon_id' => $request->integer('tambon_id') ?: null]),
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Village::class);
        $data = $this->validated($request);
        $data['source'] = 'เพิ่มโดยผู้ใช้งาน';
        $data['is_active'] = $request->boolean('is_active', true);
        Village::create($data);

        return redirect()->route('villages.index', ['tambon_id' => $data['tambon_id']])
            ->with('status', 'เพิ่มหมู่บ้านเรียบร้อยแล้ว');
    }

    public function edit(Village $village)
    {
        $this->authorize('update', $village);
        $village->load('tambon.district');

        return view('villages.form', [
            'village' => $village,
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function update(Request $request, Village $village)
    {
        $this->authorize('update', $village);
        $data = $this->validated($request, $village);
        $data['is_active'] = $request->boolean('is_active');
        $village->update($data);

        return redirect()->route('villages.index', ['tambon_id' => $data['tambon_id']])
            ->with('status', 'แก้ไขข้อมูลหมู่บ้านเรียบร้อยแล้ว');
    }

    private function validated(Request $request, ?Village $village = null): array
    {
        return $request->validate([
            'tambon_id' => ['required', 'exists:tambons,id'],
            'village_no' => ['nullable', 'integer', 'between:1,99'],
            'name_th' => [
                'required', 'string', 'max:255',
                Rule::unique('villages')->where(fn ($q) => $q
                    ->where('tambon_id', $request->integer('tambon_id'))
                    ->where('village_no', $request->input('village_no')))
                    ->ignore($village),
            ],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
    }
}
