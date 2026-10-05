<?php

namespace App\Http\Requests;

use App\Models\BranchDisposalRecord;
use App\Models\HouseholdBaselineInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseholdBaselineInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // สิทธิ์ตรวจใน HouseholdBaselineInputController ผ่าน HouseholdBaselinePolicy::update
    }

    public function rules(): array
    {
        $kind = $this->input('input_kind');

        return [
            'input_kind' => ['required', Rule::in(array_keys(HouseholdBaselineInput::KIND_LABELS))],
            'material_id' => [
                Rule::requiredIf(in_array($kind, [HouseholdBaselineInput::KIND_FERTILIZER, HouseholdBaselineInput::KIND_PESTICIDE], true)),
                'nullable',
                'exists:materials,id',
            ],
            'disposal_route' => [
                Rule::requiredIf($kind === HouseholdBaselineInput::KIND_BRANCH_ROUTE),
                'nullable',
                Rule::in(array_keys(BranchDisposalRecord::ROUTE_LABELS)),
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999.9999'],
            'moisture_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
