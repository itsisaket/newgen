<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lite F08 Market Validation master table - see the buyers migration's
 * doc-comment for scope (just "who bought it", not the full demand/LOI/
 * MOU module).
 */
class Buyer extends Model
{
    protected $fillable = ['name', 'phone', 'contact', 'buyer_type', 'standard_required', 'notes'];

    public function harvestRecords()
    {
        return $this->hasMany(HarvestRecord::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * F08 full - declared market demand from this buyer (Blueprint 10.3 -
     * distinct from actual sales, see MarketValidation's doc-comment).
     */
    public function marketValidations()
    {
        return $this->hasMany(MarketValidation::class)->latest('valid_from');
    }

    /**
     * F08 full - actual recorded sales to this buyer (Blueprint ค.5
     * sales.buyer_id).
     */
    public function sales()
    {
        return $this->hasMany(Sale::class)->latest('sale_date');
    }
}
