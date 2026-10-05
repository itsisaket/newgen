<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'ปุ๋ยอินทรีย์', 'unit' => 'กก.', 'category' => 'fertilizer', 'default_price' => 15],
            ['name' => 'ปุ๋ยเคมี 15-15-15', 'unit' => 'กก.', 'category' => 'fertilizer', 'n_pct' => 15, 'p2o5_pct' => 15, 'k2o_pct' => 15, 'default_price' => 22],
            // CFP: ปุ๋ยที่ใช้จริงในโครงการ (ระบบชาร์จไบโอชาร์) - ตัวเลขบนกระสอบ = N-P2O5-K2O
            ['name' => 'ปุ๋ยเคมี 16-16-16', 'unit' => 'กก.', 'category' => 'fertilizer', 'n_pct' => 16, 'p2o5_pct' => 16, 'k2o_pct' => 16, 'default_price' => null],
            ['name' => 'ปุ๋ยยูเรีย 46-0-0', 'unit' => 'กก.', 'category' => 'fertilizer', 'n_pct' => 46, 'p2o5_pct' => 0, 'k2o_pct' => 0, 'is_urea' => true, 'default_price' => null],
            // CFP: วัสดุพลังงาน/น้ำ - ให้กิจกรรม F13 (หมวดน้ำ/พลังงาน) บันทึกปริมาณจริง (kWh/ลิตร/ลบ.ม.) ได้ผ่าน material + quantity
            ['name' => 'ไฟฟ้า (kWh)', 'unit' => 'kWh', 'category' => 'energy', 'cfp_code' => 'electricity', 'default_price' => null],
            ['name' => 'น้ำมันดีเซล - เครื่องอยู่กับที่ เช่น เครื่องสูบน้ำ (ลิตร)', 'unit' => 'ลิตร', 'category' => 'energy', 'cfp_code' => 'diesel_stationary', 'default_price' => null],
            ['name' => 'น้ำมันดีเซล - รถ/เครื่องจักรเคลื่อนที่ (ลิตร)', 'unit' => 'ลิตร', 'category' => 'energy', 'cfp_code' => 'diesel_mobile', 'default_price' => null],
            ['name' => 'น้ำมันเบนซิน - เครื่องอยู่กับที่ เช่น เครื่องพ่นยา (ลิตร)', 'unit' => 'ลิตร', 'category' => 'energy', 'cfp_code' => 'gasoline_stationary', 'default_price' => null],
            ['name' => 'น้ำมันเบนซิน - รถ/เครื่องจักรเคลื่อนที่ (ลิตร)', 'unit' => 'ลิตร', 'category' => 'energy', 'cfp_code' => 'gasoline_mobile', 'default_price' => null],
            ['name' => 'น้ำประปา (ลบ.ม.)', 'unit' => 'ลูกบาศก์เมตร', 'category' => 'water', 'cfp_code' => 'water', 'default_price' => null],
            ['name' => 'สารป้องกันเชื้อรา', 'unit' => 'ลิตร', 'category' => 'chemical', 'default_price' => 350],
            ['name' => 'น้ำส้มควันไม้', 'unit' => 'ลิตร', 'category' => 'biomass_product', 'default_price' => 40],
            ['name' => 'ไบโอชาร์', 'unit' => 'กก.', 'category' => 'biomass_product', 'default_price' => 12],
            // 16 ก.ย. round ("แบบเบา" wood-supply tracking): เศษกิ่ง/ไม้ทุเรียนจากการตัดแต่งกิ่ง (F13)
            // เป็น material ที่เลือกตอนบันทึกกิจกรรม "ตัดแต่งกิ่ง" เพื่อให้ปริมาณไม้ (กก.) มีหน่วยชัดเจน
            // ค้นหา/รวมยอดได้ - ยังไม่ได้ผูกอัตโนมัติกับ KilnBatch.biomass_input_kg (F03) ยังต้องกะเทียบเอง
            // default_price = 0 เพราะเป็นผลพลอยได้จากการตัดแต่ง ไม่ใช่ปัจจัยที่ซื้อมา
            ['name' => 'เศษกิ่ง/ไม้ทุเรียนจากการตัดแต่งกิ่ง', 'unit' => 'กก.', 'category' => 'biomass_input', 'default_price' => 0],
        ];

        foreach ($items as $item) {
            $material = Material::firstOrCreate(['name' => $item['name']], $item);

            // เติมสัดส่วนธาตุอาหารให้แถวเดิมที่ยังว่าง (CFP) โดยไม่ทับค่าที่ผู้ดูแลแก้เอง
            if ($material->n_pct === null && isset($item['n_pct'])) {
                $material->update(array_intersect_key($item, array_flip(['n_pct', 'p2o5_pct', 'k2o_pct', 'is_urea'])));
            }
        }
    }
}
