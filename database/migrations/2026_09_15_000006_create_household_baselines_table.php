<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F01 - Household Baseline (Blueprint section 6, Appendix C.3).
// crop_season_id here refers to the pre-project baseline season.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('crop_season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->decimal('total_income', 12, 2)->nullable();
            $table->text('management_practice_notes')->nullable();

            // Workflow (Blueprint 16.1 / Appendix D) - always change via
            // WorkflowService, never assign $model->status directly.
            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->constrained('household_baselines')->nullOnDelete();

            $table->timestamps();

            // Not a unique constraint: MySQL treats every NULL in a unique
            // index as distinct, so it can't by itself guarantee "one
            // active (original_record_id IS NULL) baseline per household
            // per season" once revisions exist. Enforce that rule in the
            // controller/FormRequest instead.
            $table->index(['household_id', 'crop_season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_baselines');
    }
};
