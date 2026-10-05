<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F15 - Carbon Activity Monitoring (Blueprint Appendix C.6). Master data:
// the CO2e-per-unit coefficient for each App\Models\CarbonActivity
// category, versioned by effective date range rather than edited in
// place - "ห้ามแก้ factor_value ที่เคยถูกใช้คำนวณไปแล้ว" (a later correction to a
// published emission factor must never silently reshuffle every past
// carbon_calculations row that already cited the old value, the same
// "never edit a number a report already relied on" rule this project
// applies to KilnBatch/inventory once Approved).
//
// effective_to is nullable ("still the current value"); when a new
// factor for the same category is stored, EmissionFactorController
// closes out the previous open-ended row by setting its effective_to to
// the day before the new row's effective_from, rather than deleting or
// overwriting it - so CarbonCalculationService can always look up
// "whichever version was in force on the activity's date", even for
// activities recorded against a since-superseded factor.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('factor_value', 12, 6);
            $table->string('unit');
            $table->string('source')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['category', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
    }
};
