<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Manual ledger movements only - 'production' and 'farm_use' are always
 * written by KilnBatchController/ProductUsageController instead (they
 * have their own source records to reference), so those two types are
 * deliberately excluded from the allow-list here. As of the Sprint 4
 * round, 'sale' is excluded too - SaleController is now the single place
 * a bioproduct sale is recorded (it captures price/buyer/revenue for F06
 * and calls InventoryLedgerService itself), so the ledger and the
 * sales/revenue record can never disagree. Use "+ บันทึกการขาย" on the
 * household page instead of this form for a sale.
 */
class StoreInventoryTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 'adjustment' is further restricted to Project Admin/Super Admin
        // when it's balance-DECREASING inside InventoryLedgerService - see
        // that Service's doc-comment. Here it's just gated to staff at
        // all (never OWN_HOUSEHOLD) since a correction of the record,
        // even a positive one, shouldn't be something a household can
        // self-serve.
        $allowedTypes = ['transfer', 'loss'];

        if ($this->user()?->hasAnyRole(Role::STAFF)) {
            $allowedTypes[] = 'adjustment';
        }

        return [
            'household_id' => ['required', 'exists:households,id'],
            'product_id' => ['required', 'exists:products,id'],
            'transaction_type' => ['required', Rule::in($allowedTypes)],
            'quantity' => ['required', 'numeric'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000', 'required_if:transaction_type,adjustment'],
        ];
    }
}
