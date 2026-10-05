<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP - รอบการผลิตระดับแปลง (ตามที่ผู้ใช้กำหนด 24 ก.ย. 2569)
 *
 * รอบการผลิต = วันถัดจากการเก็บเกี่ยวผลสุดท้ายของรอบก่อน -> วันที่เก็บเกี่ยวผลสุดท้ายจริงของรอบนี้
 * (วันที่ 31 ส.ค. เป็นเพียงวันปิดรอบมาตรฐานเบื้องต้น ให้ยึดวันเก็บเกี่ยวจริงของแต่ละแปลงเป็นหลัก)
 * กิจกรรมหลังผลสุดท้ายถือเป็นของรอบถัดไป -> การจัดกิจกรรมเข้ารอบใช้ "activity_date" เทียบช่วงวันที่ของรอบ
 * ไม่ใช้ farm_activities.crop_season_id (ฤดูผลิตเดิมเป็นปีปฏิทิน 1 ม.ค.-31 ธ.ค. ซึ่งไม่ตรงนิยามนี้)
 *
 * plot_pre_bearing_emissions: การปล่อยสะสมช่วงก่อนให้ผลผลิต (เตรียมพื้นที่ ปลูก ดูแลก่อนออกผล)
 * ปันส่วนเฉลี่ยตลอดอายุการให้ผลผลิตเชิงเศรษฐกิจ: E_prebearing / plots.economic_life_years ต่อรอบ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            // ปีที่ให้ผลผลิตครั้งแรก และอายุการให้ผลผลิตเชิงเศรษฐกิจ (ปี) - ต้องได้ค่าจากทีมโครงการ/ผู้ทวนสอบ
            $table->unsignedSmallInteger('first_bearing_year')->nullable()->after('planting_year');
            $table->unsignedSmallInteger('economic_life_years')->nullable()->after('first_bearing_year');
        });

        Schema::create('plot_production_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            // ป้ายอ้างอิงปีการผลิต (ไม่ใช้กำหนดช่วงวันที่ของรอบ)
            $table->foreignId('crop_season_id')->nullable()->constrained('crop_seasons')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();          // null = รอบยังเปิดอยู่
            $table->date('last_harvest_date')->nullable(); // วันเก็บเกี่ยวผลสุดท้ายจริง (ต้อง <= end_date)
            $table->string('close_basis')->nullable();     // last_harvest | standard_cutoff (31 ส.ค.)
            $table->string('status')->default('open');     // open | closed
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['plot_id', 'status']);
            $table->unique(['plot_id', 'start_date']);
        });

        Schema::create('plot_pre_bearing_emissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->decimal('total_kgco2e', 14, 4);
            $table->string('data_basis');                  // recorded (มีบันทึกจริง) | estimated (ประมาณการ)
            $table->json('breakdown_json')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->constrained('plot_pre_bearing_emissions')->nullOnDelete();
            $table->timestamps();
            $table->index('plot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plot_pre_bearing_emissions');
        Schema::dropIfExists('plot_production_cycles');
        Schema::table('plots', function (Blueprint $table) {
            $table->dropColumn(['first_bearing_year', 'economic_life_years']);
        });
    }
};
