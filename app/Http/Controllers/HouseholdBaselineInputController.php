<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseholdBaselineInputRequest;
use App\Models\HouseholdBaseline;
use App\Models\HouseholdBaselineInput;
use App\Models\Material;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * CFP - รายการปัจจัยของ Baseline เชิงปริมาณ (ระดับครัวเรือน) ดู doc-comment ของ migration 2026_09_24_000008
 * เพิ่ม/ลบได้เฉพาะขณะ Baseline ยังเป็นร่าง และต้องมีสิทธิ์ update ของ HouseholdBaselinePolicy
 * (ขอบเขตพื้นที่ตรวจในนั้นแล้ว) - เมื่อส่งตรวจ/อนุมัติแล้วแก้ผ่าน workflow เดิมเท่านั้น
 */
class HouseholdBaselineInputController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreHouseholdBaselineInputRequest $request, HouseholdBaseline $householdBaseline)
    {
        $this->authorize('update', $householdBaseline);
        abort_unless($householdBaseline->status === 'draft', 403, 'แก้ไขได้เฉพาะข้อมูลสถานะร่างเท่านั้น');

        $data = $request->validated();
        $kind = $data['input_kind'];

        if (in_array($kind, [HouseholdBaselineInput::KIND_FERTILIZER, HouseholdBaselineInput::KIND_PESTICIDE], true)) {
            $expectedCategory = $kind === HouseholdBaselineInput::KIND_FERTILIZER ? 'fertilizer' : 'chemical';
            $material = Material::findOrFail($data['material_id']);

            if ($material->category !== $expectedCategory) {
                return back()->withInput()->withErrors(['material_id' => 'ชนิดวัสดุไม่ตรงกับประเภทปัจจัยที่เลือก']);
            }

            $data['unit'] = $material->unit;
            $data['disposal_route'] = null;
            $data['moisture_pct'] = null;
        } else {
            $data['unit'] = HouseholdBaselineInput::FIXED_UNITS[$kind];
            $data['material_id'] = null;

            if ($kind !== HouseholdBaselineInput::KIND_BRANCH_ROUTE) {
                $data['disposal_route'] = null;
                $data['moisture_pct'] = null;
            }
        }

        $householdBaseline->inputs()->create($data);

        return back()->with('status', 'เพิ่มรายการปัจจัย Baseline เรียบร้อย');
    }

    public function destroy(HouseholdBaseline $householdBaseline, HouseholdBaselineInput $input)
    {
        $this->authorize('update', $householdBaseline);
        abort_unless($householdBaseline->status === 'draft', 403, 'แก้ไขได้เฉพาะข้อมูลสถานะร่างเท่านั้น');
        abort_unless($input->household_baseline_id === $householdBaseline->id, 404);

        $input->delete();

        return back()->with('status', 'ลบรายการปัจจัย Baseline แล้ว');
    }
}
