<?php

namespace Database\Seeders;

use App\Models\CfpParameter;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ค่าเริ่มต้นพารามิเตอร์ CFP (ผู้ใช้ไม่มีข้อมูลวัดจริง 24 ก.ย. 2569 → ใช้ค่าอ้างอิงพร้อมป้ายความน่าเชื่อถือ)
 * ไม่ลงทะเบียนใน DatabaseSeeder - รันเอง: php artisan db:seed --class=CfpParameterSeeder
 * ทุกแถว verification_status = unverified จนกว่าจะเทียบต้นฉบับ/ผู้ทวนสอบเห็นชอบ
 */
class CfpParameterSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()->orderBy('id')->first();
        if (! $creator) {
            $this->command?->warn('ไม่พบผู้ใช้ในระบบ - รัน AdminUserSeeder ก่อน');

            return;
        }

        $rows = [
            [
                'code' => 'GWP_CH4', 'name' => 'GWP100 ของ CH4', 'value' => 28, 'unit' => 'kgCO2e/kgCH4',
                'confidence' => 'literature', 'source' => 'IPCC AR5 (2013) GWP100 ไม่รวม climate-carbon feedback - ให้ตรงกับ LCIA method ของไฟล์ EF อบก. (IPCC 2013 GWP100a)',
                'notes' => 'ต้องยืนยันกับ PCR/อบก. ว่าใช้ AR5 หรือ AR6',
            ],
            [
                'code' => 'GWP_N2O', 'name' => 'GWP100 ของ N2O', 'value' => 265, 'unit' => 'kgCO2e/kgN2O',
                'confidence' => 'literature', 'source' => 'IPCC AR5 (2013) GWP100 ไม่รวม climate-carbon feedback',
                'notes' => 'ต้องยืนยันกับ PCR/อบก. ว่าใช้ AR5 หรือ AR6',
            ],
            [
                'code' => 'KILN_CH4_PER_KG_BIOCHAR', 'name' => 'CH4 จากเตาผลิตไบโอชาร์ (กิ่งแห้ง)', 'value' => 5.5, 'unit' => 'g CH4/kg ไบโอชาร์',
                'sensitivity_low' => 0, 'sensitivity_high' => 179,
                'confidence' => 'literature', 'source' => 'Emission Factors for Biochar Production from Various Biomass Types in Flame Curtain Kilns, Applied Sciences 2024, 14(21), 9649 (กิ่ง/ซัง/หญ้าแห้ง ~10% ความชื้น: "<5.5"; แกลบกาแฟชื้น 12%: 179)',
                'applies_to' => 'วัดในเตา flame curtain (Kon-Tiki) ไม่ตรงกับเตาถังน้ำมัน 200 ลิตรพร้อมถังกลั่นของโครงการ',
                'notes' => 'ค่าที่ตั้งเป็นขอบบนของกรณีวัสดุแห้ง; ช่วงกว้างมากตามความชื้นและชนิดวัสดุ; ควรวัดจริงถ้าจะยื่นรับรอง',
            ],
            [
                'code' => 'KILN_N2O_PER_KG_BIOCHAR', 'name' => 'N2O จากเตาผลิตไบโอชาร์', 'value' => null, 'unit' => 'g N2O/kg ไบโอชาร์',
                'confidence' => 'unknown', 'source' => 'งานวิจัยข้างต้นไม่รายงาน N2O (รายงาน NOx < 0.13 g/kg ไบโอชาร์)',
                'notes' => 'ยังไม่มีค่า - ระบบต้องแสดงว่าตัดพจน์ N2O ของเตาออกและลด Data Quality',
            ],
            [
                'code' => 'BRANCH_MOISTURE_PCT', 'name' => 'ความชื้นเฉลี่ยของกิ่งที่ตัดแต่ง', 'value' => null, 'unit' => '% น้ำหนักสด',
                'confidence' => 'unknown', 'source' => 'ยังไม่มีข้อมูล - ให้วัดเอง (ชั่งตัวอย่างสด → ตากหรืออบจนน้ำหนักคงที่ → ชั่งซ้ำ ประมาณ 3 ตัวอย่างต่อฤดู)',
                'notes' => 'ถ้าบันทึกความชื้นในแต่ละรายการ (branch_disposal_records.moisture_pct) ให้ใช้ค่านั้นก่อน',
            ],
            [
                'code' => 'OPEN_BURN_CH4', 'name' => 'CH4 จากการเผาเศษพืชกลางแจ้ง', 'value' => 2.7, 'unit' => 'g CH4/kg น้ำหนักแห้งที่ไหม้',
                'confidence' => 'literature', 'verification_status' => 'unverified',
                'source' => 'IPCC 2006 Vol.4 Ch.2 Table 2.5 (Agricultural residues) - ค่าจากความจำ ยังตรวจต้นฉบับไม่ได้',
                'notes' => 'ต้องตรวจตารางต้นฉบับ และหา combustion factor (Table 2.6) ที่เหมาะกับกิ่งไม้ผล',
            ],
            [
                'code' => 'OPEN_BURN_N2O', 'name' => 'N2O จากการเผาเศษพืชกลางแจ้ง', 'value' => 0.07, 'unit' => 'g N2O/kg น้ำหนักแห้งที่ไหม้',
                'confidence' => 'literature', 'verification_status' => 'unverified',
                'source' => 'IPCC 2006 Vol.4 Ch.2 Table 2.5 (Agricultural residues) - ค่าจากความจำ ยังตรวจต้นฉบับไม่ได้',
            ],
            [
                'code' => 'SOIL_N2O_EF1', 'name' => 'สัดส่วน N2O-N โดยตรงจากปุ๋ยไนโตรเจนสังเคราะห์ (EF1)', 'value' => 0.01, 'unit' => 'kg N2O-N/kg N',
                'sensitivity_low' => 0.005, 'sensitivity_high' => 0.016,
                'confidence' => 'literature', 'verification_status' => 'unverified',
                'source' => 'IPCC 2006 Vol.4 Ch.11 (0.01); IPCC 2019 Refinement แยกเขตชื้น ≈ 0.016 - ค่า 2019 ยังตรวจต้นฉบับไม่ได้',
                'notes' => 'ไทยอยู่เขตชื้น - เลือกตาม PCR/ผู้ทวนสอบ',
            ],
            [
                'code' => 'UREA_CO2_PER_KG', 'name' => 'CO2 ที่ปล่อยจากการใช้ยูเรีย', 'value' => 0.733, 'unit' => 'kgCO2/kg ยูเรีย',
                'confidence' => 'literature', 'verification_status' => 'unverified',
                'source' => 'IPCC 2006 Vol.4 Ch.11 (0.20 tC/t ยูเรีย x 44/12) - ค่าจากความจำ ยังตรวจต้นฉบับไม่ได้',
            ],
            [
                'code' => 'ECONOMIC_LIFE_YEARS', 'name' => 'อายุการให้ผลผลิตเชิงเศรษฐกิจของสวนทุเรียน', 'value' => 25, 'unit' => 'ปี',
                'sensitivity_low' => 20, 'sensitivity_high' => 30,
                'confidence' => 'assumed', 'source' => 'ผู้ใช้กำหนดชั่วคราว 24 ก.ย. 2569 จนกว่าผู้ทวนสอบยืนยัน',
                'applies_to' => 'ค่าเริ่มต้น - ปรับรายแปลงได้ที่ plots.economic_life_years',
            ],
            [
                'code' => 'ALLOCATION_METHOD_KILN', 'name' => 'วิธีปันส่วนการปล่อยของเตาไปยังไบโอชาร์/น้ำส้มควันไม้', 'value' => null, 'value_text' => 'mass',
                'confidence' => 'assumed', 'source' => 'ค่าเริ่มต้น "ตามมวล" (อธิบายผู้ทวนสอบง่ายที่สุด) - ผู้ใช้เห็นชอบ 24 ก.ย. 2569',
                'notes' => 'ทางเลือก: economic (ตามมูลค่า), energy (ตามพลังงาน) - แสดงผลเปรียบเทียบเป็น Sensitivity',
            ],
        ];

        foreach ($rows as $row) {
            CfpParameter::updateOrCreate(
                ['code' => $row['code']],
                $row + ['verification_status' => 'unverified', 'created_by' => $creator->id]
            );
        }
    }
}
