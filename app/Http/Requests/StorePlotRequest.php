<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Plot registration (Blueprint section 7.1 / Appendix C.2 `plots`). Plot is
 * the main analysis unit that F13 (Farm Activity), F14 (Yield Forecast),
 * and harvest_records all point back to - master/reference data, same
 * reasoning as StoreHouseholdRequest (no workflow here).
 *
 * No gps_lat/gps_lng here - a plot's location is always its parent farm's
 * (see the 15 ก.ย. migration removing those columns from `plots`); use
 * farms.gps_lat/gps_lng instead.
 */
class StorePlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => ['required', 'exists:farms,id'],
            'plot_code' => ['required', 'string', 'max:50', 'unique:plots,plot_code'],
            'area_rai' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'tree_count' => ['nullable', 'integer', 'min:0'],
            'durian_variety_id' => ['nullable', 'exists:durian_varieties,id'],
            'planting_year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'irrigation_type' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
