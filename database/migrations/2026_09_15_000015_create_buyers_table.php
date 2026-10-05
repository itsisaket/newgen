<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lite slice of F08 Market Validation (Blueprint section 10.3 / Appendix
// C.5) - just enough of a `buyers` master table to give harvest_records
// (F14/F08-lite round) and inventory_transactions (F03/F04/Inventory,
// Sprint 3 round) a real buyer_id to point at instead of a placeholder.
// The full F08 module (demand vs. actual sales, LOI/MOU, standards) is
// still Sprint 5 scope - this table only records "who bought it", not
// demand/negotiation data.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('buyer_type')->nullable(); // เช่น ล้ง/พ่อค้าคนกลาง/สหกรณ์/ผู้บริโภคตรง
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
