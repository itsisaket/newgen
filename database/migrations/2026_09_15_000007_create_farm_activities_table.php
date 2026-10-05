<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F13 - Farm Activity Log (Blueprint section 7.2 / Appendix C.2).
// One of the offline-first field forms (Blueprint 3.2): client_uuid is
// generated on the device so records created while offline can be
// deduplicated once they sync.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_uuid')->unique();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->foreignId('crop_season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->foreignId('activity_type_id')->constrained('activity_types');
            $table->date('activity_date');
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('labor_hours', 6, 2)->nullable();
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();

            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->constrained('farm_activities')->nullOnDelete();

            $table->timestamps();

            $table->index(['plot_id', 'crop_season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_activities');
    }
};
