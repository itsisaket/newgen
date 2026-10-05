<?php

namespace App\Http\Requests;

use App\Models\BranchDisposalRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchDisposalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_uuid' => ['nullable', 'uuid'],
            'plot_id' => ['required', 'exists:plots,id'],
            'disposal_date' => ['required', 'date', 'before_or_equal:today'],
            'disposal_route' => ['required', Rule::in(array_keys(BranchDisposalRecord::ROUTE_LABELS))],
            'quantity_kg' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'moisture_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'kiln_batch_id' => ['nullable', 'exists:kiln_batches,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
