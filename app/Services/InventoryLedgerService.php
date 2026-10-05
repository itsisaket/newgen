<?php

namespace App\Services;

use App\Exceptions\InventoryLedgerException;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Blueprint section 8.3 - the ONE place allowed to write to
 * inventory_transactions. Every movement is its own row (never just an
 * update to a running total), so the ledger equation
 *   Opening + Production - FarmUse - Sale - Transfer - Loss ± Adjustment = Closing
 * can always be reconstructed from history - balance_after on each row is
 * a cached checkpoint of that running total at the time it was written,
 * computed here, never passed in by the caller.
 *
 * "ระบบต้องปฏิเสธการบันทึก Farm Use/Sale ที่ทำให้ยอดคงเหลือติดลบ เว้นแต่มี
 * Adjustment ที่ได้รับอนุญาตจาก Project Admin เท่านั้น" (Blueprint 8.3) -
 * enforced in record() below: any outbound movement (farm_use/sale/
 * transfer/loss) that would take the balance below zero is rejected
 * outright, and a negative adjustment additionally requires the actor to
 * hold Project Admin or Super Admin.
 */
class InventoryLedgerService
{
    /**
     * Current balance for a product+household pair - the balance_after of
     * its most recent transaction, or 0 if none yet.
     */
    public function balance(int $productId, int $householdId): float
    {
        $last = InventoryTransaction::where('product_id', $productId)
            ->where('household_id', $householdId)
            ->latest('id')
            ->first();

        return $last ? (float) $last->balance_after : 0.0;
    }

    /**
     * Record one ledger movement. $quantity is always given as a positive
     * magnitude EXCEPT for 'adjustment', which may be signed (a positive
     * adjustment corrects the balance up, a negative one corrects it
     * down) - this method is what turns quantity + type into the actual
     * balance delta, never the caller.
     */
    public function record(
        Product $product,
        int $householdId,
        string $type,
        float $quantity,
        \DateTimeInterface|string $transactionDate,
        User $recordedBy,
        ?Model $reference = null,
        ?int $buyerId = null,
        ?string $notes = null,
    ): InventoryTransaction {
        if (! in_array($type, InventoryTransaction::TYPES, true)) {
            throw new InventoryLedgerException("ประเภทการเคลื่อนไหวสต็อกไม่ถูกต้อง: {$type}");
        }

        if ($type !== 'adjustment' && $quantity <= 0) {
            throw new InventoryLedgerException('ปริมาณต้องมากกว่า 0');
        }

        if ($type === 'adjustment' && $quantity < 0 && ! $recordedBy->hasAnyRole([Role::PROJECT_ADMIN, Role::SUPER_ADMIN])) {
            throw new InventoryLedgerException('เฉพาะ Project Admin/Super Admin เท่านั้นที่ปรับยอดคงเหลือลง (Adjustment ติดลบ) ได้');
        }

        return DB::transaction(function () use ($product, $householdId, $type, $quantity, $transactionDate, $recordedBy, $reference, $buyerId, $notes) {
            // Lock the product+household's transaction history for the
            // duration of this write so two concurrent movements (e.g. two
            // field staff recording farm_use at the same moment) can't both
            // read the same starting balance and both pass the
            // non-negative check against a balance that's already stale by
            // the time either one commits.
            $current = InventoryTransaction::where('product_id', $product->id)
                ->where('household_id', $householdId)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $currentBalance = $current ? (float) $current->balance_after : 0.0;

            $delta = match ($type) {
                'production' => $quantity,
                'farm_use', 'sale', 'transfer', 'loss' => -$quantity,
                'adjustment' => $quantity, // already signed
            };

            $newBalance = round($currentBalance + $delta, 2);

            if ($newBalance < 0) {
                throw new InventoryLedgerException(
                    "ยอดคงเหลือไม่พอ (คงเหลือ {$currentBalance}, พยายามหัก ".abs($delta).") - ".
                    'การบันทึกที่ทำให้สต็อกติดลบทำไม่ได้ เว้นแต่เป็น Adjustment โดย Project Admin/Super Admin'
                );
            }

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'household_id' => $householdId,
                'transaction_type' => $type,
                'quantity' => $quantity,
                'transaction_date' => $transactionDate,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'balance_after' => $newBalance,
                'buyer_id' => $buyerId,
                'notes' => $notes,
                'recorded_by' => $recordedBy->id,
            ]);
        });
    }
}
