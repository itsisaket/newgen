<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAreaAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // enforced in UserAreaAssignmentController via UserPolicy
    }

    public function rules(): array
    {
        return [
            'scope_type' => ['required', 'in:province,district,tambon,farmer_group,all'],
            'province_id' => ['required_if:scope_type,province', 'nullable', 'exists:provinces,id'],
            'district_id' => ['required_if:scope_type,district', 'nullable', 'exists:districts,id'],
            'tambon_id' => ['required_if:scope_type,tambon', 'nullable', 'exists:tambons,id'],
            'farmer_group_id' => ['required_if:scope_type,farmer_group', 'nullable', 'exists:farmer_groups,id'],
        ];
    }
}
