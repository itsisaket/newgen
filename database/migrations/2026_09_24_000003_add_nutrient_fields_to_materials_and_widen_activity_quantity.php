<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CFP: คำนวณ N2O จากปุ๋ยต้องรู้สัดส่วนธาตุอาหารของปุ๋ยแต่ละชนิด
 *  - n_pct / p2o5_pct / k2o_pct: % โดยน้ำหนักของ N, P2O5, K2O (สูตร 15-15-15 = 15/15/15)
 *  - is_urea: ปุ๋ยยูเรียปล่อย CO2 เพิ่ม (IPCC) จึงต้องแยกธง
 *  - active_ingredient_pct: % สารออกฤทธิ์ของสารเคมีเกษตร (ใช้แปลงเป็น kg a.i.)
 * และขยาย farm_activities.quantity จากทศนิยม 2 -> 4 ตำแหน่ง ให้รองรับปริมาณสารเคมีเล็ก ๆ
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('n_pct', 5, 2)->nullable()->after('category');
            $table->decimal('p2o5_pct', 5, 2)->nullable()->after('n_pct');
            $table->decimal('k2o_pct', 5, 2)->nullable()->after('p2o5_pct');
            $table->boolean('is_urea')->default(false)->after('k2o_pct');
            $table->decimal('active_ingredient_pct', 5, 2)->nullable()->after('is_urea');
        });

        Schema::table('farm_activities', function (Blueprint $table) {
            $table->decimal('quantity', 12, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('farm_activities', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->nullable()->change();
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['n_pct', 'p2o5_pct', 'k2o_pct', 'is_urea', 'active_ingredient_pct']);
        });
    }
};
