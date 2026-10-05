<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP (ISO 14067) - ขยาย emission_factors ให้ตรวจย้อนได้ตามข้อกำหนด อบก.
 * ทุกคอลัมน์ nullable เพื่อไม่กระทบข้อมูล F15 เดิม
 *  - code: รหัส EF (เช่น EF-GRID, EF-UREA) ให้สูตร CFP อ้างอิงด้วยรหัสแทน category
 *  - gas_basis: CO2e / CO2 / CH4 / N2O (ค่าที่เก็บเป็นก๊าซใด)
 *  - gwp_version: เช่น AR5 / AR6 (ตามที่ อบก. กำหนด)
 *  - source_doc_version: เวอร์ชันเอกสาร EF ต้นทาง เช่น "TGO EF CFP ก.ค. 2569 (update 060769)"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emission_factors', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->string('gas_basis')->nullable()->after('unit');
            $table->string('gwp_version')->nullable()->after('gas_basis');
            $table->string('source_doc_version')->nullable()->after('source');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::table('emission_factors', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropColumn(['code', 'gas_basis', 'gwp_version', 'source_doc_version']);
        });
    }
};
