<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInnovatorEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level auth middleware handles login; role-based
        // restriction (e.g. Evaluator/Researcher-only) is TODO along with
        // the rest of the project's Permission Matrix (Blueprint Appendix
        // E item 3), same as every other F0x module today.
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'evaluation_date' => ['required', 'date'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'result' => ['required', 'in:pass,fail'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
