<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesLocationHierarchy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Farm registration (Blueprint section 7.1 / Appendix C.2 `farms`).
 * Master/reference data, same reasoning as StoreHouseholdRequest - no
 * Draft/Submitted/Verified/Approved workflow here.
 */
class StoreFarmRequest extends FormRequest
{
    use ValidatesLocationHierarchy;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            // farm_code is no longer entered by hand - assigned
            // automatically in FarmController::store() via
            // App\Support\CodeGenerator, now that farm_name is the primary
            // display name for a farm.
            // farm_name is now the primary display name (see farms/index,
            // show, and every household/farm/plot select across the app),
            // so it's required going forward - existing farms created
            // before this change keep falling back to farm_code wherever
            // farm_name happens to be blank.
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
