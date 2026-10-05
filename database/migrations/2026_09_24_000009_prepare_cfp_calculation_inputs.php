<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP v0.1 - เตรียมตารางสำหรับ CfpCalculationService
 *  - materials.cfp_code: ระบุวัสดุพลังงาน/น้ำที่ผูกกับ EF (electricity, diesel_stationary, diesel_mobile,
 *    gasoline_stationary, gasoline_mobile, water) เพื่อให้กิจกรรม F13 บันทึก kWh/ลิตร/ลบ.ม. ได้ผ่าน material + quantity เดิม
 *  - cfp_calculations: ผลคำนวณผูกกับ "รอบการผลิตระดับแปลง" (ช่วงวันที่) ไม่ใช่ฤดูผลิตปีปฏิทิน จึงให้ crop_season_id เว้นว่างได้
 *    เพิ่ม period_start/period_end, plot_production_cycle_id, คะแนนคุณภาพข้อมูล, คำเตือน และผล Sensitivity
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('cfp_code')->nullable()->after('category');
        });

        Schema::table('cfp_calculations', function (Blueprint $table) {
            $table->unsignedBigInteger('crop_season_id')->nullable()->change();
            $table->foreignId('plot_production_cycle_id')->nullable()->after('crop_season_id')
                ->constrained('plot_production_cycles')->nullOnDelete();
            $table->date('period_start')->nullable()->after('plot_production_cycle_id');
            $table->date('period_end')->nullable()->after('period_start');
            $table->decimal('data_quality_score', 5, 2)->nullable()->after('gwp_version');
            $table->json('warnings_json')->nullable()->after('ef_snapshot_json');
            $table->json('sensitivity_json')->nullable()->after('warnings_json');
            $table->index(['plot_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::table('cfp_calculations', function (Blueprint $table) {
            $table->dropIndex(['plot_id', 'period_start', 'period_end']);
            $table->dropConstrainedForeignId('plot_production_cycle_id');
            $table->dropColumn(['period_start', 'period_end', 'data_quality_score', 'warnings_json', 'sensitivity_json']);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('cfp_code');
        });
    }
};
