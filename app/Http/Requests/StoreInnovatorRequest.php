<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Manual registration of a no-household innovator/community leader
 * (Blueprint Appendix C.3: "household_id เป็น nullable เพื่อรองรับนวัตกร/แกนนำที่
 * ไม่มีครัวเรือนในระบบ") - a household-linked row is instead auto-created by
 * InnovatorEvaluationController::store() and never goes through this form.
 */
class StoreInnovatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['nullable', 'exists:households,id', Rule::unique('innovators', 'household_id')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'level' => ['nullable', 'string', 'max:100'],
            'registered_at' => ['required', 'date'],
        ];
    }
}
