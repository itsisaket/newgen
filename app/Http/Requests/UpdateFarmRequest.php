<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesLocationHierarchy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFarmRequest extends FormRequest
{
    use ValidatesLocationHierarchy;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * No `household_id` rule here on purpose (15 ก.ย. round 2 - sequential
     * entry hardening): a farm's household is fixed at creation and never
     * editable afterwards, so this key is simply never validated/accepted
     * - $request->validated() will never contain it even if a crafted
     * request includes one, and farms/edit.blade.php shows the household
     * as read-only info instead of a select. See FarmController::update().
     */
    public function rules(): array
    {
        return [
            // farm_code is assigned once at creation and never editable
            // afterwards (see StoreFarmRequest) - the edit form only ever
            // shows it as a read-only reference.
            // See StoreFarmRequest - farm_name is the primary display name now.
            'farm_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'tambon_id' => ['nullable', 'exists:tambons,id'],
            'total_area_rai' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'water_source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateLocationHierarchy($validator);
    }
}
