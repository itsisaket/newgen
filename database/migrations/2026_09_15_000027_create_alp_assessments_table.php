<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F07 - ALP Assessment (Blueprint หัวข้อ 12.1 / Appendix C.3). Tracks a
 * household's adoption of ONE specific technology through the 5-level
 * scale (see App\Models\AlpAssessment::LEVEL_LABELS) over time - a
 * household typically gets re-assessed on the same technology as it
 * progresses from "รับรู้" to "ถ่ายทอด/ขยายผล", so this is an append-only
 * history table (like durian_phenology_records), not a single current
 * value.
 *
 * Keyed to household_id directly (not innovator_id) - this exactly
 * matches Blueprint's own Appendix C.3 column list for alp_assessments
 * (id, household_id, technology_id, assessment_date, alp_level, assessor_id,
 * notes). No status/recorded_by/workflow columns in that list either, so
 * - like durian_phenology_records and innovator_evaluations - this is
 * recorded directly with no Draft/Submitted/Verified/Approved workflow.
 * recorded_by is added anyway (not in the literal Blueprint list) for the
 * same "who entered this" audit trail every other direct-record table in
 * this codebase carries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alp_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained('technologies');
            $table->date('assessment_date');
            $table->unsignedTinyInteger('alp_level');
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['household_id', 'technology_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alp_assessments');
    }
};
