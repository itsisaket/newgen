<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F04/Inventory - master product/output type list (Blueprint Appendix C.4
// `products`: "เช่น น้ำส้มควันไม้ ไบโอชาร์ ถ่านชาร์จ ถ่านกัมมันต์").
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit'); // เช่น ลิตร, กิโลกรัม
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
