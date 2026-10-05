<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F08 full - Market Validation (Blueprint หัวข้อ 10.3 / Appendix C.5).
 * Records a BUYER'S DECLARED DEMAND ("ความต้องการตลาด") - deliberately NOT
 * an actual transaction, which is what the separate `sales` table is for
 * (หัวข้อ 10.3: "'ความต้องการตลาด' แยกจาก sales"). No household/seller
 * reference here at all - this is the buyer side of the market, independent
 * of which household(s) end up fulfilling it.
 *
 * No status/recorded_by/workflow columns in Blueprint's own column list,
 * so - like sales/alp_assessments - this is a direct record, not a
 * Draft/Submitted/Verified/Approved item. recorded_by is still added for
 * the same audit-trail reason as every other direct-record table here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
            $table->enum('product_type', ['durian', 'bioproduct']);
            $table->decimal('demand_quantity', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('frequency')->nullable();
            $table->boolean('has_loi_mou')->default(false);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_validations');
    }
};
