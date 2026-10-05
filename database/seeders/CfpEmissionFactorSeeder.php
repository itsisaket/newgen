<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ค่า EF สำหรับ CFP สวนทุเรียน (ขอบเขตถึงประตูสวน)
 * อ้าง: อบก. Emission Factor (CFP) UPDATE 6 ก.ค. 2569 (บังคับใช้ตั้งแต่ 1 ต.ค. 2569)
 * แหล่ง: Thai National LCI Database, TIIS-MTEC-NSTDA (with TGO Update_April2026), LCIA IPCC 2013 GWP100a V1.03 (= AR5)
 *
 * ตรวจครบ 12 แถวกับไฟล์ PDF ต้นฉบับแล้วเมื่อ 24 ก.ย. 2569 (เลขลำดับในไฟล์อยู่ในคอลัมน์แรกของแต่ละแถว)
 * ไม่ได้ลงทะเบียนใน DatabaseSeeder โดยตั้งใจ - รันเองด้วย
 *    php artisan db:seed --class=CfpEmissionFactorSeeder
 *
 * ข้อควรระวังการนำไปคำนวณ (สำหรับ CfpCalculationService):
 *  1) EF น้ำมัน (ลำดับ 39, 42) คือ "กระบวนการกลั่นน้ำมันดิบ" ต่อ kg - ไม่รวมการเผาไหม้ และไฟล์ CFP นี้
 *     ไม่มีแถวการเผาไหม้ ต้องหาจากไฟล์ EF ชุด CFO ของ อบก. เพิ่ม + ระบบเก็บเป็นลิตร ต้องมีความหนาแน่นแปลงเป็น kg
 *  2) EF เครื่องจักร (449, 466, 476) เป็นค่าต่อชั่วโมง/ตร.ม./ลบ.ม. จากฐาน LCI ครอบคลุมการผลิตเครื่องและการใช้งาน
 *     (แถวเครื่องพ่นยาสะพายหลังระบุใช้เชื้อเพลิงในช่วงใช้งาน) → อาจรวมเชื้อเพลิงแล้ว ห้ามนับซ้ำกับ EF น้ำมัน/ไฟฟ้า
 *     ต้องยืนยันกับ อบก./ผู้ทวนสอบ; รถกระบะ (67-70) ระบุใช้น้ำมันดีเซลเป็นเชื้อเพลิง = ใช้แทนการนับน้ำมันของรถคันนั้นเอง
 *  3) EF-REF-DURIAN-LCI (359) ข้อมูลจากการทบทวนวรรณกรรม ครอบคลุมเตรียมดิน-ปลูก-ดูแล-เก็บเกี่ยว-หลังเก็บเกี่ยว
 *     ใช้เป็น benchmark เท่านั้น ห้ามบวกเข้ายอดปล่อย (category = cfp_benchmark_reference)
 *  4) ไฟล์นี้ไม่มี EF การผลิตปุ๋ยเคมี/ยูเรีย/สารกำจัดศัตรูพืช/ไบโอชาร์ (พบเฉพาะปุ๋ยหมักจากมูลฝอย ลำดับ 484-486
 *     ซึ่งไม่ใช่ปุ๋ยอินทรีย์ที่ใช้ในสวน) → ต้องหาจากแหล่งอื่น
 *  5) วันมีผล: ไฟล์นี้ "บังคับใช้ตั้งแต่ 1 ต.ค. 2569" - ต้องตกลงกับผู้ทวนสอบว่ากิจกรรมของรอบผลิตที่เกิดก่อนวันนั้นใช้ชุด EF ใด
 *     (CfpCalculationService ควรเลือก EF ตาม "ชุดเวอร์ชันที่ผู้ทวนสอบยืนยัน" ไม่ใช่ activity_date)
 */
class CfpEmissionFactorSeeder extends Seeder
{
    private const DOC = 'อบก. EF (CFP) UPDATE 6 ก.ค. 2569 (บังคับใช้ 1 ต.ค. 2569) / TIIS-MTEC-NSTDA (with TGO Update_April2026)';

    public function run(): void
    {
        $creator = User::query()->orderBy('id')->first();
        if (! $creator) {
            $this->command?->warn('ไม่พบผู้ใช้ในระบบ - รัน AdminUserSeeder ก่อน');

            return;
        }

        $rows = [
            ['EF-GRID', 'cfp_electricity', 0.5562, 'kgCO2e/kWh', '[ลำดับ 44] ไฟฟ้า Grid Mix 2022-2024'],
            ['EF-WATER-PWA', 'cfp_water', 0.5167, 'kgCO2e/m3', '[ลำดับ 46] น้ำประปา กปภ.'],
            ['EF-DIESEL-PROD', 'cfp_fuel_production', 0.3503, 'kgCO2e/kg', '[ลำดับ 42] น้ำมันดีเซล - การผลิตน้ำมัน (ไม่รวมการเผาไหม้)'],
            ['EF-GASOLINE-PROD', 'cfp_fuel_production', 0.4006, 'kgCO2e/kg', '[ลำดับ 39] น้ำมันเบนซิน - การผลิตน้ำมัน (ไม่รวมการเผาไหม้)'],
            ['EF-PUMP-15HP', 'cfp_machinery', 0.0551, 'kgCO2e/m3', '[ลำดับ 476] เครื่องสูบน้ำเกษตร 15 hp'],
            ['EF-TRACTOR-35HP', 'cfp_machinery', 23.9727, 'kgCO2e/hr', '[ลำดับ 449] รถแทรกเตอร์ 35 hp'],
            ['EF-SPREADER-35HP', 'cfp_machinery', 0.0012, 'kgCO2e/m2', '[ลำดับ 466] เครื่องหว่านปุ๋ยเม็ด 35 hp'],
            ['EF-PICKUP-0', 'cfp_transport', 0.3130, 'kgCO2e/km', '[ลำดับ 67] รถกระบะ 4 ล้อ ปกติ 0% load'],
            ['EF-PICKUP-50', 'cfp_transport', 0.2697, 'kgCO2e/tkm', '[ลำดับ 68] รถกระบะ 4 ล้อ ปกติ 50% load'],
            ['EF-PICKUP-75', 'cfp_transport', 0.1839, 'kgCO2e/tkm', '[ลำดับ 69] รถกระบะ 4 ล้อ ปกติ 75% load'],
            ['EF-PICKUP-100', 'cfp_transport', 0.1410, 'kgCO2e/tkm', '[ลำดับ 70] รถกระบะ 4 ล้อ ปกติ 100% load'],
            ['EF-REF-DURIAN-LCI', 'cfp_benchmark_reference', 0.2412, 'kgCO2e/kg', '[ลำดับ 359] ทุเรียน - ค่าอ้างอิง LCI (benchmark เท่านั้น ห้ามบวกซ้ำ)'],
        ];

        foreach ($rows as [$code, $category, $value, $unit, $name]) {
            EmissionFactor::updateOrCreate(
                ['code' => $code, 'effective_from' => '2026-10-01'],
                [
                    'category' => $category,
                    'factor_value' => $value,
                    'unit' => $unit,
                    'gas_basis' => 'CO2e',
                    'gwp_version' => 'AR5 (IPCC 2013 GWP100a V1.03)',
                    'source' => $name,
                    'source_doc_version' => self::DOC.' [ตรวจกับ PDF ต้นฉบับแล้ว 24 ก.ย. 2569]',
                    'created_by' => $creator->id,
                ]
            );
        }
    }
}
