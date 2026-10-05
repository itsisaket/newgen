<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Inventory Ledger (Blueprint section 8.3 / Appendix C.4
// `inventory_transactions`). Every stock movement is its own row - never
// just an updated running total - so
// Opening + Production - FarmUse - Sale - Transfer - Loss ± Adjustment = Closing
// can always be proven from history. See App\Services\InventoryLedgerService,
// the single place allowed to write to this table.
//
// `quantity` is always stored positive except for transaction_type =
// 'adjustment', which may be signed (a correction can go either
// direction) - the Service is what turns `quantity` + `transaction_type`
// into the actual balance delta, never the caller.
// `reference_type`/`reference_id` point back at whatever produced this
// movement (KilnBatch for production, ProductUsage for farm_use, or null
// for a manually-recorded sale/transfer/loss/adjustment) - a lightweight
// polymorphic reference, not a real morphTo FK, to avoid every future
// reference table needing a matching foreign key here.
// `buyer_id` (lite F08 addition - see the buyers migration) is only set
// for transaction_type = 'sale'.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('transaction_type'); // production / farm_use / sale / transfer / loss / adjustment
            $table->decimal('quantity', 10, 2);
            $table->date('transaction_date');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 12, 2);
            $table->foreignId('buyer_id')->nullable()->constrained('buyers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['product_id', 'household_id', 'transaction_date'], 'inv_trans_product_household_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
