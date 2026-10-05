<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Real check happens in the controller via
        // $this->authorize('create', [...]) - matches every other
        // locked-parent module in this codebase.
        return true;
    }

    public function rules(): array
    {
        return [
            'innovator_id' => ['required', 'integer', 'exists:innovators,id'],
            'transfer_date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'recipient_count' => ['required', 'integer', 'min:1'],
            'recipient_type' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
        ];
    }
}
