<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F14 - Durian Yield Forecast, data-entry half only (Blueprint section
// 10.1 / Appendix C.2 `durian_phenology_records`). This round adds the
// screen to LOG phenology observations per plot (flowering -> full bloom
// -> fruit set -> fruit development, with fruit count / expected weight /
// expected harvest date) - the actual Forecast Yield rollup service
// (Plot -> Household -> Tambon -> District -> Province, Calibration
// Factor, Forecast Accuracy per Blueprint 10.2) is still Sprint 5 scope
// and needs several seasons of harvest_records history to mean anything.
// No Draft/Submitted/Verified/Approved workflow here on purpose - this is
// periodic field observation logging (like a diary), not a record that
// gates money/approval the way F01/F13/F14-harvest do; kept simple the
// same way InnovatorEvaluation (F09-lite) is.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('durian_phenology_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->foreignId('crop_season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->string('stage'); // flowering / full_bloom / fruit_set / fruit_development
            $table->date('observed_date');
            $table->unsignedInteger('fruit_count')->nullable();
            $table->decimal('expected_avg_weight_kg', 6, 2)->nullable();
            $table->date('expected_harvest_date')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['plot_id', 'crop_season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('durian_phenology_records');
    }
};
