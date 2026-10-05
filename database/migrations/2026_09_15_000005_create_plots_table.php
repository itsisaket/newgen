<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Blueprint section 7.1 / Appendix C.2. Plot is the main analysis unit -
// almost every F01-F15 record eventually points back to a plot_id.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('plot_code')->unique();
            $table->decimal('area_rai', 8, 2)->nullable();
            $table->unsignedInteger('tree_count')->nullable();
            $table->foreignId('durian_variety_id')->nullable()->constrained('durian_varieties')->nullOnDelete();
            $table->unsignedSmallInteger('planting_year')->nullable();
            $table->string('irrigation_type')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};
