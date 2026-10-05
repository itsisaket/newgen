<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sprint 5 Role/Permission Matrix audit (DRFIS-Sprint5-Design-16Sep.md
     * ส่วน A, กลุ่ม 2 Core Registry): "Farmer/Innovator แก้ได้เฉพาะครัวเรือน
     * ตัวเอง (ข้อมูลติดต่อเท่านั้น ไม่ใช่ farmer_group/village)". Found during
     * that audit that HouseholdPolicy::update() already lets a household's
     * own Farmer/Innovator login through, but this request had NO
     * field-level restriction - a hand-crafted POST could reassign
     * farmer_group_id/village_id (moving the household out of a Field
     * Officer's AreaScopeService scope entirely) or flip status to
     * withdrawn/active as a self-service action, neither of which is
     * "contact info". Stripped here, before validation, rather than only
     * hidden in the form - see resources/views/households/edit.blade.php
     * for the matching UI change.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()?->hasAnyRole(Role::OWN_HOUSEHOLD)) {
            $this->request->remove('farmer_group_id');
            $this->request->remove('village_id');
            $this->request->remove('status');
        }
    }

    public function rules(): array
    {
        return [
            // household_code is assigned once at creation and never
            // editable afterwards (see StoreHouseholdRequest) - the edit
            // form only ever shows it as a read-only reference.
            'head_name' => ['required', 'string', 'max:255'],
            'id_card_number' => ['nullable', 'digits:13'],
            'phone' => ['nullable', 'string', 'max:20'],
            // sometimes: absent entirely for OWN_HOUSEHOLD (see
            // prepareForValidation() above), so "required" only bites a
            // staff submission that dropped the field some other way.
            'farmer_group_id' => ['sometimes', 'nullable', 'exists:farmer_groups,id'],
            'village_id' => ['sometimes', 'nullable', 'exists:villages,id'],
            'registered_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', 'in:active,inactive,withdrawn'],
        ];
    }
}
