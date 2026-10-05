<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F11 Knowledge Transfer (Blueprint Appendix C.5 `knowledge_transfers`).
// Sprint 5 round - see claude project doc DRFIS-Sprint5-Design-16Sep.md
// for the full design writeup.
//
// One deviation from the Blueprint's literal column list: the Data
// Dictionary lists a single `evidence_id` (nullable) FK column, but every
// other module in this codebase attaches evidence through the polymorphic
// `evidences` table (evidenceable_type/evidenceable_id - see Evidence.php)
// instead of a dedicated FK column, which also allows more than one photo
// per record. Kept consistent with that established pattern here instead
// of adding a one-off single-evidence column - see StoreEvidenceRequest's
// allow-list, which this round adds KnowledgeTransfer to.
//
// `recorded_by` is not in the Blueprint's literal column list either, but
// every other field-data table in this codebase tracks who recorded it
// (farm_activities.recorded_by, carbon_activities.recorded_by,
// alp_assessments.assessor_id, ...) - added for the same reason, per the
// Blueprint's own note that Appendix C. is a baseline teams should extend
// during actual migration work.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innovator_id')->constrained('innovators');
            $table->date('transfer_date');
            $table->string('topic');
            $table->unsignedInteger('recipient_count');
            // Free text, e.g. "เกษตรกรในพื้นที่" / "นักเรียน" / "หน่วยงานภายนอก" -
            // not a DB enum, matches this codebase's convention of validating
            // loosely-typed strings at the FormRequest layer instead.
            $table->string('recipient_type');
            $table->string('location');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['innovator_id', 'transfer_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_transfers');
    }
};
