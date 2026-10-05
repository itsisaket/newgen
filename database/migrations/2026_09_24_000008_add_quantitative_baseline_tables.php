<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP - Baseline เชิงปริมาณ (ระดับครัวเรือน ตามที่ผู้ใช้ตัดสินใจ 24 ก.ย. 2569)
 * F01 เดิมเก็บแค่ต้นทุน/รายได้ - ตารางนี้เก็บ "ปริมาณต่อปี" ของปัจจัยการผลิตและเส้นทางจัดการกิ่งก่อนเข้าโครงการ
 * เพื่อเทียบกับรอบปัจจุบันได้ (ต่อไร่ / ต่อ kg ผลผลิต) และพิสูจน์การลดการใช้ปุ๋ย/สารเคมี/การเผากิ่ง
 *  - household_baselines.baseline_area_rai / baseline_harvest_kg: ตัวหารสำหรับเทียบต่อไร่/ต่อ kg
 *  - household_baseline_inputs: แถวละ 1 รายการปัจจัย (ปุ๋ยแต่ละชนิด สารเคมีแต่ละชนิด ไฟฟ้า น้ำมัน น้ำ เส้นทางกิ่ง)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('household_baselines', function (Blueprint $table) {
            $table->decimal('baseline_area_rai', 10, 2)->nullable()->after('total_income');
            $table->decimal('baseline_harvest_kg', 12, 2)->nullable()->after('baseline_area_rai');
        });

        Schema::create('household_baseline_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_baseline_id')->constrained('household_baselines')->cascadeOnDelete();
            $table->string('input_kind');                 // fertilizer|pesticide|electricity|diesel|gasoline|water|branch_route
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('disposal_route')->nullable(); // เฉพาะ input_kind = branch_route
            $table->decimal('quantity', 14, 4);           // ปริมาณต่อปี (ตามหน่วยด้านล่าง)
            $table->string('unit');
            $table->decimal('moisture_pct', 5, 2)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index('household_baseline_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_baseline_inputs');
        Schema::table('household_baselines', function (Blueprint $table) {
            $table->dropColumn(['baseline_area_rai', 'baseline_harvest_kg']);
        });
    }
};
