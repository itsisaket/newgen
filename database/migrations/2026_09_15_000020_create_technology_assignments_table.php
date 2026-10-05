<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F12 - allocation history of a technology_asset to a household
// (Blueprint 8.1 "ประวัติการจัดสรร"). No DB-level constraint preventing two
// simultaneously-active rows for the same asset (MySQL partial/filtered
// unique indexes aren't straightforward) - TechnologyAssignmentController
// enforces "an asset must be returned before it can be reassigned" at the
// application layer instead - see that controller's doc-comment.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technology_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technology_asset_id')->constrained('technology_assets')->cascadeOnDelete();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->date('returned_date')->nullable();
            $table->string('status')->default('active'); // active / returned
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();

            $table->index(['technology_asset_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technology_assignments');
    }
};
