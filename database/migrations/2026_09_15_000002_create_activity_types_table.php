<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Blueprint section 7.2 / Appendix C.2 - groups used to analyse
// farm_activities (fertilizer / chemical / labor / water_energy /
// biomass_product / harvest_transport).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->enum('category', [
                'fertilizer', 'chemical', 'labor', 'water_energy', 'biomass_product', 'harvest_transport',
            ]);
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_types');
    }
};
