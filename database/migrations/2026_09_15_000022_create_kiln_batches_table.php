<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F03 - Kiln Production Log (Blueprint section 8.1 / Appendix C.4
// `kiln_batches`). Shares the same Draft -> Submitted -> Verified ->
// Approved workflow as F13/F08-harvest (Blueprint 16.1) - see
// KilnBatchController's doc-comment for why the resulting
// inventory_transactions (production) are only written at the Approve
// step, not at creation.
//
// operator_id (Blueprint: "อ้างอิง users หรือ innovators ตามการออกแบบ Auth
// จริง") is the person who physically ran the kiln that day - this
// project's real Auth only ever has `users` rows (a นวัตกรชุมชน operating
// a kiln IS a User with the Innovator role, not a separate `innovators`
// table - Blueprint's alp_assessments/competency_* Appendix C.3 tables
// for a *separate* `innovators` entity are still Sprint 4 scope and not
// built yet), so this points at users.id. `recorded_by` is separate -
// WorkflowService's required ownership column - because the person
// logging the batch into the system isn't always the same person who
// physically operated the kiln (a Field Officer may transcribe a
// household's paper log).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiln_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technology_asset_id')->constrained('technology_assets');
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('batch_code')->unique();
            $table->foreignId('operator_id')->constrained('users');
            $table->date('batch_date');
            $table->decimal('biomass_input_kg', 10, 2);
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->decimal('energy_cost', 10, 2)->nullable();
            $table->decimal('other_cost', 10, 2)->nullable();
            $table->decimal('production_time_hours', 6, 2)->nullable();

            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->constrained('kiln_batches')->nullOnDelete();

            $table->timestamps();

            $table->index(['household_id', 'batch_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiln_batches');
    }
};
