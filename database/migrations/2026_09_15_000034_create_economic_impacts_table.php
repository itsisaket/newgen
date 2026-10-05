<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F06 - Economic Impact (Blueprint หัวข้อ 11 / Appendix C.5). "สร้างโดย
 * EconomicImpactService เท่านั้น ห้าม insert/update ตรงจากผู้ใช้" - no
 * FormRequest/Controller ever writes here directly, only
 * App\Services\EconomicImpactService::calculateForHousehold(), which
 * updateOrCreate()s keyed on (household_id, crop_season_id) so opening the
 * report always refreshes this household+season's ONE row rather than
 * accumulating duplicate snapshots - calculated_at/calculation_version
 * are what let a later formula change be told apart from an old number,
 * per the Blueprint's own note that the calc formula may be versioned
 * (หัวข้อ 12/Analysis doc: same reasoning as emission_factors' versioning).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economic_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('crop_season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->decimal('cost_saving', 12, 2)->default(0);
            $table->decimal('durian_income_increase', 12, 2)->default(0);
            $table->decimal('bioproduct_income', 12, 2)->default(0);
            $table->decimal('additional_technology_cost', 12, 2)->default(0);
            $table->decimal('net_benefit', 14, 2);
            $table->enum('target_status', ['achieved', 'not_achieved']);
            $table->timestamp('calculated_at');
            $table->string('calculation_version', 20)->default('v1');
            $table->timestamps();

            $table->unique(['household_id', 'crop_season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economic_impacts');
    }
};
