<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdBaselineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level auth middleware handles login; area-scope TODO per Blueprint 4.1
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'crop_season_id' => ['required', 'exists:crop_seasons,id'],
            'total_cost' => ['nullable', 'numeric', 'min:0'],
            'total_income' => ['nullable', 'numeric', 'min:0'],
            'baseline_area_rai' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'baseline_harvest_kg' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'management_practice_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
