<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Household registration (Blueprint section 3 / Appendix C.1 `households`).
 * This is master/reference data - not an F01-F15 research instrument - so
 * it does not go through WorkflowService; `status` here just tracks
 * whether the household is still an active participant.
 */
class StoreHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level auth middleware handles login; area-scope TODO per Blueprint 4.1
    }

    public function rules(): array
    {
        return [
            // household_code is no longer entered by hand - it's assigned
            // automatically in HouseholdController::store() via
            // App\Support\CodeGenerator, now that head_name is the primary
            // display name for a household.
            'head_name' => ['required', 'string', 'max:255'],
            // Plain digits in, encrypted at rest via the model's `encrypted`
            // cast on id_card_number_encrypted (Blueprint 19 - PDPA).
            'id_card_number' => ['nullable', 'digits:13'],
            'phone' => ['nullable', 'string', 'max:20'],
            'farmer_group_id' => ['nullable', 'exists:farmer_groups,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'registered_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,withdrawn'],
        ];
    }
}
