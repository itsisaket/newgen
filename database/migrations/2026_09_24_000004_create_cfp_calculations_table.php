<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP (ISO 14067) ผลคำนวณระดับ แปลง x ฤดูผลิต - แยกจาก carbon_calculations (F15) โดยตั้งใจ
 * เพราะ F15 คือ "กิจกรรมลดคาร์บอน" ส่วนตารางนี้คือ "การนับก๊าซเรือนกระจกของผลิตภัณฑ์"
 *
 * ขอบเขตตั้งต้น: cradle_to_farm_gate (ถึงประตูสวน) / หน่วยการทำงาน 1 กก. ทุเรียนสด
 * Service เป็นผู้เขียนเพียงผู้เดียว (ตามธรรมเนียมเดียวกับ CarbonCalculationService)
 * ค่าที่ไหลมาจากข้อมูล approved เท่านั้น และเก็บ ef_snapshot ให้ reproduce ได้
 * status: estimated -> verified -> certified (ไม่ใช่ workflow ร่าง/ส่ง/อนุมัติของข้อมูลดิบ)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cfp_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->foreignId('crop_season_id')->constrained('crop_seasons')->cascadeOnDelete();
            $table->string('boundary')->default('cradle_to_farm_gate');
            $table->string('functional_unit')->default('1 kg durian');
            $table->decimal('harvest_kg', 12, 2)->nullable();
            $table->decimal('area_rai', 10, 2)->nullable();
            $table->decimal('e_total_kgco2e', 14, 4);
            $table->decimal('cfp_per_kg', 12, 6)->nullable();
            $table->decimal('cfp_per_rai', 14, 4)->nullable();
            $table->json('breakdown_json')->nullable();
            $table->json('ef_snapshot_json')->nullable();
            $table->string('gwp_version')->nullable();
            $table->string('status')->default('estimated');
            $table->string('calculation_version');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['plot_id', 'crop_season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfp_calculations');
    }
};
