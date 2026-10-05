<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F09 - one assessment round for one innovator (Blueprint Appendix C.3:
 * id, innovator_id, round T0/T1/T2, assessment_date, assessor_type
 * self/observer, assessor_id). "ควรประเมินอย่างน้อย T0 ก่อนพัฒนา, T1 หลังการฝึก/
 * ทดลอง และ T2 หลังใช้จริง" (หัวข้อ 12.2) - round is NOT unique per innovator
 * because re-assessing the same round is allowed (e.g. a redo), reports
 * should use the LATEST row per (innovator_id, round) rather than assume
 * exactly one.
 *
 * No status/workflow columns, same as alp_assessments/innovator_evaluations
 * - a direct record, not a Draft/Submitted/Verified/Approved item.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competency_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('innovator_id')->constrained('innovators')->cascadeOnDelete();
            $table->enum('round', ['T0', 'T1', 'T2']);
            $table->date('assessment_date');
            $table->enum('assessor_type', ['self', 'observer']);
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['innovator_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competency_assessments');
    }
};
