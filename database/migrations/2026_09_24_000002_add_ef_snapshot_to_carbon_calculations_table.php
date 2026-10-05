<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เก็บ snapshot ของค่า EF ณ วันคำนวณ เพื่อให้ผลลัพธ์ย้อนตรวจได้แม้ตาราง
 * emission_factors ถูกแก้ภายหลัง (ข้อกำหนดการทวนสอบ) - nullable สำหรับแถวเก่า
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carbon_calculations', function (Blueprint $table) {
            $table->decimal('factor_value_snapshot', 12, 6)->nullable()->after('co2e_kg');
            $table->string('factor_unit_snapshot')->nullable()->after('factor_value_snapshot');
            $table->string('factor_source_snapshot')->nullable()->after('factor_unit_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('carbon_calculations', function (Blueprint $table) {
            $table->dropColumn(['factor_value_snapshot', 'factor_unit_snapshot', 'factor_source_snapshot']);
        });
    }
};
