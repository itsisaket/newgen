<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Blueprint section 8.3 Inventory Ledger. Never create rows on this model
 * directly - always go through App\Services\InventoryLedgerService so
 * balance_after is always computed consistently and the
 * negative-balance-unless-adjustment rule is never bypassed.
 */
class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id', 'household_id', 'transaction_type', 'quantity', 'transaction_date',
        'reference_type', 'reference_id', 'balance_after', 'buyer_id', 'notes', 'recorded_by',
    ];

    public const TYPES = ['production', 'farm_use', 'sale', 'transfer', 'loss', 'adjustment'];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'quantity' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function productUsage()
    {
        return $this->hasOne(ProductUsage::class);
    }

    /**
     * Whichever KilnBatch/ProductUsage/etc. this transaction traces back
     * to, if any - a lightweight polymorphic lookup (see the migration's
     * doc-comment for why this isn't a real morphTo).
     */
    public function reference(): ?Model
    {
        return $this->reference_type && $this->reference_id
            ? $this->reference_type::find($this->reference_id)
            : null;
    }
}
