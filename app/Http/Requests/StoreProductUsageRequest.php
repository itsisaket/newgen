<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductUsageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plot_id' => ['required', 'exists:plots,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'usage_rate' => ['nullable', 'numeric', 'min:0'],
            'application_method' => ['nullable', 'string', 'max:255'],
            'application_date' => ['required', 'date'],
        ];
    }
}
