<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Blueprint Appendix C.2 - used later to check Forecast Accuracy (F14) and
// as an input to economic_impacts (F06). Table only for now; F14/F06
// controllers land in a later Sprint.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvest_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->foreignId('crop_season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->date('harvest_date');
            $table->decimal('actual_weight_kg', 10, 2);
            $table->string('grade')->nullable();
            $table->decimal('price_per_kg', 10, 2)->nullable();
            $table->foreignId('buyer_id')->nullable(); // buyers table lands with F08 Market Validation
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_records');
    }
};
