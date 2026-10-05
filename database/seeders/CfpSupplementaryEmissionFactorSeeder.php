<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * EF เสริมสำหรับ CFP ที่ไม่มีในไฟล์ EF (CFP) ก.ค. 2569 ของ อบก. - ผู้ใช้อนุญาตให้อ้างแหล่งอื่นที่อ้างอิงได้ (24 ก.ย. 2569)
 * ไม่ได้ลงทะเบียนใน DatabaseSeeder - รันเอง: php artisan db:seed --class=CfpSupplementaryEmissionFactorSeeder
 *
 * สถานะทุกแถว = "รอตรวจกับเอกสารต้นฉบับ" - ตัวเลขได้จากการอ่านหน้าเว็บ/ตารางสรุป ไม่ใช่จากไฟล์ต้นฉบับโดยตรง
 *  A) เผาไหม้น้ำมัน: อบก. EF (CFO) มกราคม 2569 (อ้าง IPCC 2006 Vol.2 Ch.2 Table 2.3) - ต่อลิตร จึงไม่ต้องใช้ความหนาแน่น
 *     ส่วน EF การผลิตน้ำมัน (CfpEmissionFactorSeeder, ต่อ kg) ยังต้องใช้ความหนาแน่นแปลงจากลิตรเป็น kg (ต้องหาแหล่งอ้างอิง)
 *     ต้องระวัง: ใช้ "การเผาไหม้ + การผลิตน้ำมัน" รวมกันเป็นวงจรชีวิตของน้ำมัน และไม่รวมกับ EF เครื่องจักรที่อาจรวมน้ำมันแล้ว
 *  B) ปุ๋ย/สารเคมี: ตารางรวมของ 4C Services (List of Emission Factors, 2025) ซึ่งอ้าง Brentrup et al. 2018 และ
 *     EC Standard values 2015 - ควรไปตรวจต้นทาง (Fertilizers Europe / EC) ก่อนใช้รายงาน
 *     * EF ปุ๋ยไนโตรเจนต่างกันมากระหว่างแหล่ง (1.99 vs 5.89 kgCO2e/kg N) และเป็นค่ายุโรป/ค่ากำหนดตามกฎหมาย
 *       ไม่ใช่ค่าเฉพาะประเทศไทย (ยูเรียที่ไทยนำเข้าอาจมี footprint สูงกว่า) → ให้ใช้เป็นช่วง Sensitivity
 *     * แต่ละสูตรปุ๋ยคำนวณแบบองค์ประกอบ: kg N x EF_N + kg P2O5 x EF_P2O5 + kg K2O x EF_K2O
 */
class CfpSupplementaryEmissionFactorSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()->orderBy('id')->first();
        if (! $creator) {
            $this->command?->warn('ไม่พบผู้ใช้ในระบบ - รัน AdminUserSeeder ก่อน');

            return;
        }

        $cfo = 'อบก. EF (CFO) มกราคม 2569 (อ้าง IPCC 2006 Vol.2 Ch.2 Tab.2.3) [รอตรวจไฟล์ต้นฉบับ]';
        $brentrup = 'Brentrup et al. 2018 ผ่านตาราง 4C Services List of EF 2025 [รอตรวจต้นทาง]';
        $ec = 'EC Standard values 2015 ผ่านตาราง 4C Services List of EF 2025 [รอตรวจต้นทาง]';

        // [code, category, value, unit, name, doc, effective_from]
        $rows = [
            ['EF-DIESEL-COMB-MOBILE', 'cfp_fuel_combustion', 2.740339134, 'kgCO2e/L', 'ดีเซล - เผาไหม้ แหล่งเคลื่อนที่ (รถ/เครื่องจักรเคลื่อนที่)', $cfo, '2026-01-01'],
            ['EF-DIESEL-COMB-STATIONARY', 'cfp_fuel_combustion', 2.70757206, 'kgCO2e/L', 'ดีเซล - เผาไหม้ แหล่งอยู่กับที่ (เครื่องสูบน้ำ/เครื่องกำเนิดไฟฟ้า)', $cfo, '2026-01-01'],
            ['EF-GASOLINE-COMB-MOBILE', 'cfp_fuel_combustion', 2.23734656, 'kgCO2e/L', 'เบนซิน - เผาไหม้ แหล่งเคลื่อนที่ (uncontrolled)', $cfo, '2026-01-01'],
            ['EF-GASOLINE-COMB-STATIONARY', 'cfp_fuel_combustion', 2.18921364, 'kgCO2e/L', 'เบนซิน - เผาไหม้ แหล่งอยู่กับที่ (เครื่องพ่นยา/ตัดหญ้า)', $cfo, '2026-01-01'],
            ['EF-FERT-N-UREA', 'cfp_fertilizer_production', 1.99, 'kgCO2e/kg N', 'ปุ๋ยไนโตรเจน (ยูเรีย) - การผลิต [สถานการณ์ต่ำ]', $brentrup, '2018-01-01'],
            ['EF-FERT-N-EC', 'cfp_fertilizer_production', 5.89, 'kgCO2e/kg N', 'ปุ๋ยไนโตรเจน - ค่ามาตรฐาน EC [สถานการณ์สูง/อนุรักษ์นิยม]', $ec, '2015-01-01'],
            ['EF-FERT-P2O5', 'cfp_fertilizer_production', 1.01, 'kgCO2e/kg P2O5', 'ปุ๋ยฟอสเฟต - การผลิต', $ec, '2015-01-01'],
            ['EF-FERT-K2O', 'cfp_fertilizer_production', 0.58, 'kgCO2e/kg K2O', 'ปุ๋ยโพแทช - การผลิต', $ec, '2015-01-01'],
            ['EF-PEST-GENERAL', 'cfp_pesticide', 10.97, 'kgCO2e/kg a.i.', 'สารกำจัดศัตรูพืชทั่วไป - การผลิต (ยังไม่แยกชนิด)', $ec, '2015-01-01'],
        ];

        foreach ($rows as [$code, $category, $value, $unit, $name, $doc, $from]) {
            EmissionFactor::updateOrCreate(
                ['code' => $code, 'effective_from' => $from],
                [
                    'category' => $category,
                    'factor_value' => $value,
                    'unit' => $unit,
                    'gas_basis' => 'CO2e',
                    'source' => $name,
                    'source_doc_version' => $doc,
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
