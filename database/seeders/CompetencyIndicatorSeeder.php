<?php

namespace Database\Seeders;

use App\Models\CompetencyIndicator;
use Illuminate\Database\Seeder;

/**
 * F09 - master indicator list, 1-2 per dimension per Blueprint 12.2's
 * example table. firstOrCreate keyed on (dimension, name) so `db:seed`
 * stays safe to re-run.
 */
class CompetencyIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            ['dimension' => 'knowledge', 'name' => 'หลักการเตาและกระบวนการผลิต', 'max_score' => 100],
            ['dimension' => 'knowledge', 'name' => 'การใช้ผลิตภัณฑ์ชีวมวล', 'max_score' => 100],
            ['dimension' => 'skill', 'name' => 'เตรียมวัตถุดิบและเดินเตา', 'max_score' => 100],
            ['dimension' => 'skill', 'name' => 'เก็บผลผลิตและความปลอดภัย', 'max_score' => 100],
            ['dimension' => 'adaptation', 'name' => 'แก้ปัญหาและปรับให้เหมาะกับสวน', 'max_score' => 100],
            ['dimension' => 'management', 'name' => 'คำนวณต้นทุน ผลผลิต ราคา กำไร', 'max_score' => 100],
            ['dimension' => 'market', 'name' => 'บรรจุภัณฑ์ แบรนด์ และช่องทางตลาด', 'max_score' => 100],
            ['dimension' => 'market', 'name' => 'ความเข้าใจมาตรฐานสินค้า', 'max_score' => 100],
            ['dimension' => 'transfer', 'name' => 'สาธิตและสอนผู้อื่น', 'max_score' => 100],
            ['dimension' => 'transfer', 'name' => 'เป็นพี่เลี้ยงและขยายผล', 'max_score' => 100],
        ];

        foreach ($indicators as $data) {
            CompetencyIndicator::firstOrCreate(
                ['dimension' => $data['dimension'], 'name' => $data['name']],
                ['max_score' => $data['max_score'], 'is_active' => true]
            );
        }
    }
}
