<?php

namespace App\Http\Requests;

use App\Models\CarbonActivity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmissionFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(CarbonActivity::CATEGORIES)],
            'factor_value' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:500'],
            'effective_from' => ['required', 'date'],
        ];
    }
}
