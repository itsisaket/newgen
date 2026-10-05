<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * No gps_lat/gps_lng here - a plot's location is always its parent farm's
 * (see the 15 ก.ย. migration removing those columns from `plots`); use
 * farms.gps_lat/gps_lng instead.
 *
 * No `farm_id` rule either (15 ก.ย. round 2 - sequential entry hardening):
 * a plot's farm is fixed at creation and never editable afterwards, so
 * this key is simply never validated/accepted - see
 * PlotController::update() and plots/edit.blade.php.
 */
class UpdatePlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $plotId = $this->route('plot')?->id;

        return [
            'plot_code' => ['required', 'string', 'max:50', Rule::unique('plots', 'plot_code')->ignore($plotId)],
            'area_rai' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'tree_count' => ['nullable', 'integer', 'min:0'],
            'durian_variety_id' => ['nullable', 'exists:durian_varieties,id'],
            'planting_year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'irrigation_type' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
