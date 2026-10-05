<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F15 - Carbon Activity Monitoring (Blueprint Appendix C.6 `carbon_activities`
// + section 16.1 Workflow). Raw activity log a household/field team records
// (e.g. "ใส่ถ่านชีวภาพ 200 กก." on a date) - deliberately the SAME
// Draft -> Submitted -> Verified -> Approved shape as kiln_batches (see
// that migration's doc-comment): status/rejection_reason/recorded_by/
// original_record_id are WorkflowService's required columns, and the
// resulting CO2e figure (carbon_calculations) is only computed at the
// Approve step by CarbonActivityController, mirroring how kiln_batches'
// output only becomes a real inventory_transactions row on Approve -
// never let an unverified quantity silently become a published carbon
// number.
//
// category is a free string validated against App\Models\CarbonActivity::
// CATEGORIES rather than a DB enum, matching every other lookup-style
// column in this codebase (batch status, product_type, etc.) so adding a
// category later is a one-line model change, not a migration.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('category');
            $table->date('activity_date');
            $table->decimal('quantity', 12, 4);
            $table->string('unit');
            $table->text('description')->nullable();

            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->constrained('carbon_activities')->nullOnDelete();

            $table->timestamps();

            $table->index(['household_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_activities');
    }
};
