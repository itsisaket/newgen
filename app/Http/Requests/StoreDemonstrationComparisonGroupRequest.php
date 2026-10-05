<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemonstrationComparisonGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'crop_season_id' => ['required', 'integer', 'exists:crop_seasons,id'],
            'technology_id' => ['nullable', 'integer', 'exists:technologies,id'],
            'objective' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
