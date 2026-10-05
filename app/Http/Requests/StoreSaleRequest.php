<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_household_id' => ['required', 'exists:households,id'],
            'buyer_id' => ['nullable', 'exists:buyers,id'],
            'product_type' => ['required', 'in:durian,bioproduct'],
            // Required for bioproduct (which product moved out of stock),
            // must stay empty for durian (durian itself was never a
            // tracked Product - see the sales migration's doc-comment).
            'product_id' => [
                Rule::requiredIf(fn () => $this->input('product_type') === 'bioproduct'),
                Rule::prohibitedIf(fn () => $this->input('product_type') === 'durian'),
                'nullable', 'exists:products,id',
            ],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            // total_amount is NEVER accepted from the client - see
            // SaleController::store(), which always computes it as
            // quantity * unit_price server-side.
            'sale_date' => ['required', 'date'],
            'crop_season_id' => ['required', 'exists:crop_seasons,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
