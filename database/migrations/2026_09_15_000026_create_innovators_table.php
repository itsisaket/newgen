<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F07/F09/F11 Sprint 4 round - real `innovators` table per Blueprint
 * Appendix C.3 Data Dictionary (ค.3 กลุ่ม Research):
 *   id, household_id (nullable), name, phone, level, registered_at
 *
 * Resolves the design question left open since the 15 ก.ย. F09-lite round
 * (see innovator_evaluations migration's doc-comment): the Blueprint's own
 * "โครงสร้างฐานข้อมูลระดับภาพรวม" table (หัวข้อ 17) lists `innovators` as its
 * own entity in the Research group, separate from `households`/`users`,
 * and both `competency_assessments.innovator_id` and
 * `knowledge_transfers.innovator_id` FK to innovators.id - not to
 * households.id or users.id. household_id is explicitly nullable in the
 * Data Dictionary "เพื่อรองรับนวัตกร/แกนนำที่ไม่มีครัวเรือนในระบบ" (to support an
 * innovator/community leader who has no household record in the system -
 * e.g. a district-level master trainer). So this is NOT just an alias for
 * "a Household whose User has the Innovator role" - it is its own
 * registry row, auto-created (see InnovatorEvaluationController::store())
 * the moment a household's evaluation result becomes 'pass', but also
 * directly creatable for a no-household innovator via InnovatorController.
 *
 * innovator_evaluation_id traces back to the qualifying pass evaluation
 * for households promoted the normal way - nullable because a directly
 * registered no-household innovator has no innovator_evaluations row to
 * point to. Not part of the Blueprint's literal column list but follows
 * the same "trace derived/promoted records back to their source" pattern
 * already used elsewhere (e.g. inventory_transactions.reference_type/id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('innovators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->nullable()->unique()->constrained('households')->nullOnDelete();
            $table->foreignId('innovator_evaluation_id')->nullable()->constrained('innovator_evaluations')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('level')->nullable();
            $table->date('registered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovators');
    }
};
