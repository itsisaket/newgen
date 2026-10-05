<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F05 - Demonstration Plot (no Blueprint Data Dictionary exists for this
// module - see claude project doc DRFIS-Sprint5-Design-16Sep.md ส่วน B
// for the full "why" of this schema). This table is only a REGISTRATION/
// LABEL of one comparison experiment - it does NOT duplicate activity,
// cost, or yield data, because a demo plot is still a normal `plots` row
// that keeps recording through farm_activities (F13)/farm_production_costs
// (F02)/harvest_records (F14/F08) exactly as any other plot does.
// Dashboard/Report joins demonstration_plots.plot_id back to those tables
// grouped by group_type (treatment/control) to produce the comparison.
//
// No Draft/Submitted/Verified/Approved workflow here on purpose - this is
// a "which plots are in which experiment" registry, closer to
// technology_assignments than to a field data record - see the design
// doc's ส่วน B for the full reasoning.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demonstration_comparison_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('crop_season_id')->constrained('crop_seasons');
            $table->foreignId('technology_id')->nullable()->constrained('technologies')->nullOnDelete();
            $table->text('objective')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demonstration_comparison_groups');
    }
};
