<?php

namespace App\Http\Requests;

use App\Models\AlpAssessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlpAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'technology_id' => ['required', 'exists:technologies,id'],
            'assessment_date' => ['required', 'date'],
            'alp_level' => ['required', 'integer', Rule::in(array_keys(AlpAssessment::LEVEL_LABELS))],
            'assessor_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
