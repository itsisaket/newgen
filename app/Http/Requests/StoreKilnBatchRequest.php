<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * F03 - the batch itself plus its output line items (kiln_batch_outputs)
 * in one submission, same "parent + line items in one form" pattern as
 * F13's material/quantity fields, just with a repeatable outputs[] array
 * since a batch can yield more than one product.
 */
class StoreKilnBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'technology_asset_id' => ['required', 'exists:technology_assets,id'],
            'operator_id' => ['nullable', 'exists:users,id'],
            'batch_code' => ['required', 'string', 'max:255', 'unique:kiln_batches,batch_code'],
            'batch_date' => ['required', 'date'],
            'biomass_input_kg' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'energy_cost' => ['nullable', 'numeric', 'min:0'],
            'other_cost' => ['nullable', 'numeric', 'min:0'],
            'production_time_hours' => ['nullable', 'numeric', 'min:0'],
            'outputs' => ['required', 'array', 'min:1'],
            'outputs.*.product_id' => ['required', 'exists:products,id'],
            'outputs.*.output_quantity' => ['required', 'numeric', 'min:0.01'],
            'outputs.*.unit' => ['required', 'string', 'max:50'],
            'outputs.*.quality_grade' => ['nullable', 'string', 'max:50'],
        ];
    }
}
