<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F04 - Bio-product Utilization (Blueprint Appendix C.4 `product_usages`):
// "เชื่อมการใช้ผลิตภัณฑ์ชีวมวลกับแปลงที่ใช้จริง". One row per
// inventory_transactions row of type 'farm_use' - see
// ProductUsageController/InventoryLedgerService.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_transaction_id')->unique()->constrained('inventory_transactions')->cascadeOnDelete();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->decimal('usage_rate', 10, 2)->nullable(); // เช่น ลิตร/ไร่, กก./ต้น
            $table->string('application_method')->nullable();
            $table->date('application_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_usages');
    }
};
