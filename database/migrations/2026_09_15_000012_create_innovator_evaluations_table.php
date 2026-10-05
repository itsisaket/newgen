<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simplified pass/fail gate for the เกษตรกร -> นวัตกรชุมชน (Farmer ->
 * Innovator) role upgrade the user asked for ("นอกจากนั้น เกษตรกร จะเป็น
 * นวัตกรชุมชน และ หากผ่านการประเมิน"). This is deliberately NOT the full
 * F07 ALP Assessment (5-level scale) or F09 Innovator Competency
 * (6-dimension, self/observer, T0/T1/T2) modules from Blueprint section
 * 12 - those are still unbuilt, Sprint 5+ scope. This is a placeholder
 * single-score/pass-fail record just so "หากผ่านการประเมิน" has somewhere
 * to live and something concrete to gate the role change on. Replace or
 * extend this table when F07/F09 are actually built.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('innovator_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('evaluation_date');
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('result', ['pass', 'fail']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('innovator_evaluations');
    }
};
