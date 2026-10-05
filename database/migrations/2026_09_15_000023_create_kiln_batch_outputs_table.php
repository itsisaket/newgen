<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F03 - line items of one kiln_batches row (Blueprint 8.1/8.2: "ต้นทางของ
// สมการ Biochar Yield/Wood Vinegar Yield"). A single batch can yield more
// than one product (e.g. both ไบโอชาร์ and น้ำส้มควันไม้ from the same run).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiln_batch_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kiln_batch_id')->constrained('kiln_batches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('output_quantity', 10, 2);
            $table->string('unit');
            $table->string('quality_grade')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiln_batch_outputs');
    }
};
