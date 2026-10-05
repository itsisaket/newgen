<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// F09 - child table of competency_assessments (Blueprint Appendix C.3:
// 1 assessment มีหลาย indicator). Unique per (assessment, indicator) so an
// indicator can't accidentally be scored twice within the same round.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_assessment_id')->constrained('competency_assessments')->cascadeOnDelete();
            $table->foreignId('competency_indicator_id')->constrained('competency_indicators');
            $table->decimal('score', 5, 2);
            $table->timestamps();

            $table->unique(['competency_assessment_id', 'competency_indicator_id'], 'competency_scores_unique_pair');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_scores');
    }
};
