<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP - พารามิเตอร์ที่ไม่ใช่ EF ตรง ๆ (ค่าเริ่มต้น ช่วง Sensitivity วิธีปันส่วน อายุการให้ผลผลิต ฯลฯ)
 * ทุกค่าต้องบอกระดับความน่าเชื่อถือและแหล่งอ้างอิง เพื่อให้ผลลัพธ์แสดง Data Quality ได้
 * และไม่บล็อกการคำนวณเมื่อยังไม่มีข้อมูลวัดจริง (ผู้ใช้ตัดสินใจ 24 ก.ย. 2569)
 *  confidence: measured (วัดจริงโครงการ) | literature (งานวิจัย/มาตรฐานที่อ้างอิงได้) |
 *              assumed (สมมติฐานที่ตกลงกัน) | unknown (ยังไม่มีค่า)
 *  verification_status: unverified (ยังไม่ตรวจต้นฉบับ) | verified | verifier_approved
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cfp_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('value', 16, 6)->nullable();
            $table->string('value_text')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('sensitivity_low', 16, 6)->nullable();
            $table->decimal('sensitivity_high', 16, 6)->nullable();
            $table->string('confidence')->default('unknown');
            $table->string('verification_status')->default('unverified');
            $table->text('source')->nullable();
            $table->text('applies_to')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfp_parameters');
    }
};
