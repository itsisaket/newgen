<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

// Blueprint section 7.2 - a starter list per category; the project should
// extend this to match the real Farm Activity Log form before Go-live.
class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['category' => 'fertilizer', 'name' => 'ปุ๋ยอินทรีย์'],
            ['category' => 'fertilizer', 'name' => 'ปุ๋ยเคมีสูตรเสมอ'],
            ['category' => 'chemical', 'name' => 'สารป้องกันเชื้อรา'],
            ['category' => 'chemical', 'name' => 'สารกำจัดแมลง'],
            ['category' => 'labor', 'name' => 'ตัดแต่งกิ่ง'],
            ['category' => 'labor', 'name' => 'พ่นยา/ให้ปุ๋ย'],
            ['category' => 'water_energy', 'name' => 'ค่าไฟสูบน้ำ'],
            ['category' => 'water_energy', 'name' => 'ค่าน้ำมันเครื่องสูบน้ำ'],
            ['category' => 'biomass_product', 'name' => 'ใช้น้ำส้มควันไม้'],
            ['category' => 'biomass_product', 'name' => 'ใช้ไบโอชาร์ปรับปรุงดิน'],
            ['category' => 'harvest_transport', 'name' => 'ค่าแรงเก็บเกี่ยว'],
            ['category' => 'harvest_transport', 'name' => 'ค่าขนส่งผลผลิต'],
        ];

        foreach ($items as $item) {
            ActivityType::firstOrCreate($item);
        }
    }
}
