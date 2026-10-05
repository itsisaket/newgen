<?php

namespace App\Http\Requests;

use App\Models\CarbonActivity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarbonActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'integer', 'exists:households,id'],
            'category' => ['required', 'string', Rule::in(CarbonActivity::CATEGORIES)],
            'activity_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
