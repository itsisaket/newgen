<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sprint 3: Technology & Biomass (F12 - Technology Management, Blueprint
// section 8 / Appendix C.4). `technologies` is the master type list (เช่น
// เตาผลิตถ่านชีวภาพ, ถังกลั่นน้ำส้มควันไม้) - individual physical units live
// in technology_assets (next migration), keyed to one of these types.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
