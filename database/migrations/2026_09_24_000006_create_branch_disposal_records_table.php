<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP - การจัดการกิ่ง/เศษไม้จากสวน (ตัดแต่งกิ่ง) ตามเส้นทางการกำจัด
 * ไบโอชาร์/น้ำส้มควันไม้ในโครงการนี้มีเป้าหมายลดปุ๋ย/สารเคมี และลดการเผา/ย่อยสลายกิ่ง
 * → ผลลัพธ์ต้องมาจากข้อมูลจริงของแต่ละเส้นทาง (kiln / open_burn / field_decompose / compost / other)
 * ใช้ตรวจสมดุลมวล: กิ่งที่ตัดได้ = Σ ทุกเส้นทาง และเชื่อมกับ kiln_batches เมื่อ route = kiln
 * (ไม่ใช้เครดิตสมมติ: ผลการลดการเผาเห็นจากการเปรียบเทียบสัดส่วนเส้นทางระหว่างฐานเดิมกับรอบปัจจุบัน)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_disposal_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('client_uuid')->unique();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->date('disposal_date');
            $table->string('disposal_route'); // kiln | open_burn | field_decompose | compost | other
            $table->decimal('quantity_kg', 10, 2);      // น้ำหนักสด
            $table->decimal('moisture_pct', 5, 2)->nullable(); // ใช้แปลงเป็นน้ำหนักแห้ง (dry matter) ตอนคำนวณ
            $table->foreignId('kiln_batch_id')->nullable()->constrained('kiln_batches')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('original_record_id')->nullable()->constrained('branch_disposal_records')->nullOnDelete();
            $table->timestamps();
            $table->index(['plot_id', 'disposal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_disposal_records');
    }
};
