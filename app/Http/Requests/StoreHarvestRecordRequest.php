<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHarvestRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_id' => ['required', 'exists:plots,id'],
            'crop_season_id' => ['required', 'exists:crop_seasons,id'],
            'harvest_date' => ['required', 'date'],
            'actual_weight_kg' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'grade' => ['nullable', 'string', 'max:50'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'buyer_id' => ['nullable', 'exists:buyers,id'],
        ];
    }
}
