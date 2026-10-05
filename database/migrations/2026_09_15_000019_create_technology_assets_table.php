<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F12 (Blueprint 8.1: "เทคโนโลยีทุกชุดควรมี Asset Code และประวัติการจัดสรร").
// One row per physical unit (เตา/ถัง เครื่องที่ 1, 2, 3, ...) under a
// technologies type. Allocation history lives in technology_assignments.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technology_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technology_id')->constrained('technologies')->cascadeOnDelete();
            $table->string('asset_code')->unique();
            $table->date('acquired_date')->nullable();
            $table->string('condition_status')->default('good'); // good / needs_repair / retired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technology_assets');
    }
};
