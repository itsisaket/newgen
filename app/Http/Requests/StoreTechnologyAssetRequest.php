<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnologyAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technology_id' => ['required', 'exists:technologies,id'],
            'asset_code' => ['required', 'string', 'max:255', 'unique:technology_assets,asset_code'],
            'acquired_date' => ['nullable', 'date'],
            'condition_status' => ['required', 'in:good,needs_repair,retired'],
        ];
    }
}
