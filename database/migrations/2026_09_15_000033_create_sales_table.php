<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F08 full - actual recorded sales (Blueprint ค.5: "'ยอดขายจริง' ใช้เป็น
 * แหล่งข้อมูลของ economic_impacts") - distinct from market_validations'
 * buyer-declared demand (หัวข้อ 10.3). This is the single source of truth
 * SaleController writes to for BOTH product types:
 *   - product_type = 'bioproduct': product_id required, and store() also
 *     calls InventoryLedgerService::record(type: 'sale', ...) so the
 *     inventory ledger and the sales/revenue record can never disagree -
 *     see SaleController's doc-comment. This SUPERSEDES the generic
 *     'sale' movement type on InventoryTransactionController (see that
 *     controller's StoreInventoryTransactionRequest - 'sale' removed from
 *     its allow-list in this round).
 *   - product_type = 'durian': product_id stays null - durian itself was
 *     never a tracked Product/inventory item (only biomass products are -
 *     HarvestRecord tracks weight, not stock), so a durian sale only
 *     records revenue here, no ledger movement.
 *
 * No status/workflow columns in Blueprint's own column list, so - like
 * market_validations/alp_assessments - a direct record. recorded_by
 * added for the same audit-trail reason as every other direct-record
 * table here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('buyer_id')->nullable()->constrained('buyers')->nullOnDelete();
            $table->enum('product_type', ['durian', 'bioproduct']);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 14, 2);
            $table->date('sale_date');
            $table->foreignId('crop_season_id')->constrained('crop_seasons');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['seller_household_id', 'crop_season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
