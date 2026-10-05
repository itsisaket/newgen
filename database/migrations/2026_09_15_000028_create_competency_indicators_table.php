<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F09 - Innovator Competency (Blueprint หัวข้อ 12.2 / Appendix C.3). Master
// list of scorable indicators across the 6 dimensions - see
// App\Models\CompetencyIndicator::DIMENSION_LABELS. is_active lets an
// indicator be retired from future assessments without breaking history
// on past competency_scores rows that reference it (no hard delete,
// same convention as everywhere else in this codebase).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_indicators', function (Blueprint $table) {
            $table->id();
            $table->enum('dimension', ['knowledge', 'skill', 'adaptation', 'management', 'market', 'transfer']);
            $table->string('name');
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_indicators');
    }
};
