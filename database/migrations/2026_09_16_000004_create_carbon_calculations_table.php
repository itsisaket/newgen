<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F15 - Carbon Activity Monitoring, calculation result (Blueprint Appendix
// C.6 `carbon_calculations` + section 11 "แยก Activity Data ออกจาก
// Calculation Result"). Exactly the economic_impacts convention: ONE row
// per carbon_activities row (1:1, unique carbon_activity_id), written
// ONLY by CarbonCalculationService::calculateFor() when a CarbonActivity
// is Approved - no role has direct Create/Edit/Approve on this table,
// same as economic_impacts/farm_production_costs (see EconomicImpactService's
// doc-comment for why: a hand-edited CO2e figure would desync from the
// activity+factor that's supposed to explain it).
//
// emission_factor_id records exactly WHICH factor version produced this
// number, so a later emission-factor correction never silently changes
// what a past calculation says - re-approving/recalculating (if ever
// needed) is an explicit action, not implicit.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carbon_activity_id')->unique()->constrained('carbon_activities')->cascadeOnDelete();
            $table->foreignId('emission_factor_id')->constrained('emission_factors');
            $table->decimal('co2e_kg', 14, 4);
            $table->string('calculation_version');
            $table->timestamp('calculated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_calculations');
    }
};
