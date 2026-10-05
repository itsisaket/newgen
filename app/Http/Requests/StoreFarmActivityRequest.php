<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_uuid' => ['nullable', 'uuid'],
            'plot_id' => ['required', 'exists:plots,id'],
            'crop_season_id' => ['required', 'exists:crop_seasons,id'],
            'activity_type_id' => ['required', 'exists:activity_types,id'],
            'activity_date' => ['required', 'date'],
            'material_id' => ['nullable', 'exists:materials,id'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_hours' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
