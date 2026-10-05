<?php

namespace Database\Seeders;

use App\Models\DurianVariety;
use App\Models\FarmerGroup;
use App\Models\Household;
use Illuminate\Database\Seeder;

/**
 * Sample Household -> Farm -> Plot data for the three Sisaket project
 * districts only: Khun Han, Kantharalak and Si Rattana. Household business
 * codes remain stable because dependent seeders refer to them.
 *
 * Uses updateOrCreate (not firstOrCreate) keyed on the business code, so
 * re-running `db:seed` always converges to this curated dataset - even
 * for HH-0001/FARM-0001/PLOT-0001, which the original Sprint 2 version of
 * this seeder already created with placeholder data on some installs.
 *
 * Every farm receives an approximate district-level map coordinate with a
 * small deterministic offset. These are demo coordinates, not surveyed
 * farm locations, and must be replaced when real registrations are entered.
 */
class DemoFarmSeeder extends Seeder
{
    public function run(): void
    {
        $varieties = DurianVariety::pluck('id', 'name');

        // One household per farmer group seeded by LocationSeeder, so
        // every province/district/tambon has a real example underneath it.
        // Farms alternate between one and two plots for variety.
        $households = [
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง',
                'household_code' => 'HH-0001', 'head_name' => 'สมชาย ใจดี', 'phone' => '081-234-5601',
                'registered_at' => '2019-03-15', 'status' => 'active',
                'farm_code' => 'FARM-0001', 'farm_name' => 'สวนลุงสมชาย', 'total_area_rai' => 12.5,
                'water_source' => 'บ่อบาดาล', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0001', 'area_rai' => 6, 'tree_count' => 60, 'variety' => 'หมอนทอง', 'planting_year' => 2012, 'irrigation' => 'มินิสปริงเกลอร์'],
                    ['code' => 'PLOT-0002', 'area_rai' => 6.5, 'tree_count' => 55, 'variety' => 'ชะนี', 'planting_year' => 2015, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก',
                'household_code' => 'HH-0002', 'head_name' => 'สมหญิง แซ่ตั้ง', 'phone' => '081-234-5602',
                'registered_at' => '2020-06-01', 'status' => 'active',
                'farm_code' => 'FARM-0002', 'farm_name' => 'สวนป้าสมหญิง', 'total_area_rai' => 7,
                'water_source' => 'คลองชลประทาน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0003', 'area_rai' => 7, 'tree_count' => 65, 'variety' => 'หมอนทอง', 'planting_year' => 2010, 'irrigation' => 'รดด้วยสายยาง'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย',
                'household_code' => 'HH-0003', 'head_name' => 'ประยุทธ์ สวนทุเรียน', 'phone' => '081-234-5603',
                'registered_at' => '2018-11-20', 'status' => 'active',
                'farm_code' => 'FARM-0003', 'farm_name' => 'สวนลุงประยุทธ์', 'total_area_rai' => 15,
                'water_source' => 'สระน้ำในสวน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0004', 'area_rai' => 8, 'tree_count' => 70, 'variety' => 'ก้านยาว', 'planting_year' => 2008, 'irrigation' => 'ระบบน้ำอัตโนมัติ'],
                    ['code' => 'PLOT-0005', 'area_rai' => 7, 'tree_count' => 62, 'variety' => 'หมอนทอง', 'planting_year' => 2014, 'irrigation' => 'มินิสปริงเกลอร์'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง',
                'household_code' => 'HH-0004', 'head_name' => 'มาลี ผลไม้หวาน', 'phone' => '081-234-5604',
                'registered_at' => '2021-02-10', 'status' => 'active',
                'farm_code' => 'FARM-0004', 'farm_name' => 'สวนป้ามาลี', 'total_area_rai' => 5,
                'water_source' => 'น้ำประปาหมู่บ้าน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0006', 'area_rai' => 5, 'tree_count' => 40, 'variety' => 'กระดุม', 'planting_year' => 2017, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก',
                'household_code' => 'HH-0005', 'head_name' => 'วิชัย เก็บเกี่ยวดี', 'phone' => '081-234-5605',
                'registered_at' => '2019-09-05', 'status' => 'active',
                'farm_code' => 'FARM-0005', 'farm_name' => 'สวนลุงวิชัย', 'total_area_rai' => 10,
                'water_source' => 'บ่อบาดาล', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0007', 'area_rai' => 5, 'tree_count' => 45, 'variety' => 'พวงมณี', 'planting_year' => 2013, 'irrigation' => 'มินิสปริงเกลอร์'],
                    ['code' => 'PLOT-0008', 'area_rai' => 5, 'tree_count' => 42, 'variety' => 'หมอนทอง', 'planting_year' => 2016, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย',
                'household_code' => 'HH-0006', 'head_name' => 'สุดา ปลูกทุเรียนงาม', 'phone' => '081-234-5606',
                'registered_at' => '2022-01-18', 'status' => 'active',
                'farm_code' => 'FARM-0006', 'farm_name' => 'สวนป้าสุดา', 'total_area_rai' => 4.5,
                'water_source' => 'คลองชลประทาน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0009', 'area_rai' => 4.5, 'tree_count' => 38, 'variety' => 'ชะนี', 'planting_year' => 2018, 'irrigation' => 'รดด้วยสายยาง'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง',
                'household_code' => 'HH-0007', 'head_name' => 'อำนาจ ทองแท้', 'phone' => '081-234-5607',
                'registered_at' => '2017-05-22', 'status' => 'active',
                'farm_code' => 'FARM-0007', 'farm_name' => 'สวนลุงอำนาจ', 'total_area_rai' => 20,
                'water_source' => 'สระน้ำในสวน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0010', 'area_rai' => 10, 'tree_count' => 80, 'variety' => 'หมอนทอง', 'planting_year' => 2005, 'irrigation' => 'ระบบน้ำอัตโนมัติ'],
                    ['code' => 'PLOT-0011', 'area_rai' => 10, 'tree_count' => 75, 'variety' => 'ก้านยาว', 'planting_year' => 2009, 'irrigation' => 'มินิสปริงเกลอร์'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก',
                'household_code' => 'HH-0008', 'head_name' => 'รัตนา ศรีสวัสดิ์', 'phone' => '081-234-5608',
                'registered_at' => '2020-08-14', 'status' => 'active',
                'farm_code' => 'FARM-0008', 'farm_name' => 'สวนป้ารัตนา', 'total_area_rai' => 6,
                'water_source' => 'น้ำประปาหมู่บ้าน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0012', 'area_rai' => 6, 'tree_count' => 50, 'variety' => 'กระดุม', 'planting_year' => 2019, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย',
                'household_code' => 'HH-0009', 'head_name' => 'ชูชาติ บุญมาก', 'phone' => '081-234-5609',
                'registered_at' => '2016-12-01', 'status' => 'inactive',
                'farm_code' => 'FARM-0009', 'farm_name' => 'สวนลุงชูชาติ', 'total_area_rai' => 9,
                'water_source' => 'บ่อบาดาล', 'farm_status' => 'inactive',
                'plots' => [
                    ['code' => 'PLOT-0013', 'area_rai' => 4.5, 'tree_count' => 35, 'variety' => 'หมอนทอง', 'planting_year' => 2011, 'irrigation' => 'มินิสปริงเกลอร์'],
                    ['code' => 'PLOT-0014', 'area_rai' => 4.5, 'tree_count' => 33, 'variety' => 'พวงมณี', 'planting_year' => 2011, 'irrigation' => 'มินิสปริงเกลอร์'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง',
                'household_code' => 'HH-0010', 'head_name' => 'ดวงใจ รุ่งเรือง', 'phone' => '081-234-5610',
                'registered_at' => '2021-07-09', 'status' => 'active',
                'farm_code' => 'FARM-0010', 'farm_name' => 'สวนป้าดวงใจ', 'total_area_rai' => 3.5,
                'water_source' => 'คลองชลประทาน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0015', 'area_rai' => 3.5, 'tree_count' => 28, 'variety' => 'ชะนี', 'planting_year' => 2020, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก',
                'household_code' => 'HH-0011', 'head_name' => 'สมพงษ์ แสงทอง', 'phone' => '081-234-5611',
                'registered_at' => '2018-04-25', 'status' => 'active',
                'farm_code' => 'FARM-0011', 'farm_name' => 'สวนลุงสมพงษ์', 'total_area_rai' => 11,
                'water_source' => 'สระน้ำในสวน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0016', 'area_rai' => 6, 'tree_count' => 55, 'variety' => 'หมอนทอง', 'planting_year' => 2013, 'irrigation' => 'ระบบน้ำอัตโนมัติ'],
                    ['code' => 'PLOT-0017', 'area_rai' => 5, 'tree_count' => 48, 'variety' => 'กระดุม', 'planting_year' => 2017, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย',
                'household_code' => 'HH-0012', 'head_name' => 'นงลักษณ์ พูลสุข', 'phone' => '081-234-5612',
                'registered_at' => '2015-10-30', 'status' => 'withdrawn',
                'farm_code' => 'FARM-0012', 'farm_name' => 'สวนป้านงลักษณ์', 'total_area_rai' => 8,
                'water_source' => 'น้ำประปาหมู่บ้าน', 'farm_status' => 'inactive',
                'plots' => [
                    ['code' => 'PLOT-0018', 'area_rai' => 8, 'tree_count' => 60, 'variety' => 'ชะนี', 'planting_year' => 2007, 'irrigation' => 'รดด้วยสายยาง'],
                ],
            ],
            // เพิ่ม 16 ก.ย. 2569 ตามคำขอ - สวนตัวอย่างในพื้นที่จริงของโครงการ
            // (จังหวัดศรีสะเกษ อำเภอขุนหาญ/กันทรลักษ์/ศรีรัตนะ - แหล่งปลูก
            // "ทุเรียนภูเขาไฟศรีสะเกษ") พร้อมพิกัด GPS จริง เพื่อให้ Dashboard
            // "จัดการสวน" มีจุดให้แสดงบนแผนที่ในพื้นที่ทำงานจริงของระบบ (สวนตัวอย่าง
            // 12 แถวเดิมด้านบนไม่เคยมีพิกัด GPS เลย เพราะเป็นแค่พื้นที่นำร่อง Sprint
            // 1-2) - **พิกัดเป็นค่าประมาณระดับอำเภอที่ขยับเล็กน้อยให้กระจายไปทาง
            // ตำบลที่ระบุ (อ้างอิงจากพิกัดศูนย์อำเภอทางการ) ไม่ใช่พิกัดที่สำรวจสวน
            // จริงทีละแปลง** ผู้ใช้ควรแก้ไขให้ตรงพิกัดจริงภายหลังผ่านแผนที่ GPS
            // picker ที่หน้าแก้ไขสวน (มีอยู่แล้ว)
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง',
                'household_code' => 'HH-0013', 'head_name' => 'บุญมี ทุเรียนภูเขาไฟ', 'phone' => '081-234-5613',
                'registered_at' => '2022-05-12', 'status' => 'active',
                'farm_code' => 'FARM-0013', 'farm_name' => 'สวนทุเรียนภูเขาไฟลุงบุญมี', 'total_area_rai' => 14,
                'gps_lat' => 14.6050, 'gps_lng' => 104.3980,
                'water_source' => 'บ่อบาดาล', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0019', 'area_rai' => 7, 'tree_count' => 65, 'variety' => 'หมอนทอง', 'planting_year' => 2011, 'irrigation' => 'มินิสปริงเกลอร์'],
                    ['code' => 'PLOT-0020', 'area_rai' => 7, 'tree_count' => 58, 'variety' => 'ก้านยาว', 'planting_year' => 2014, 'irrigation' => 'น้ำหยด'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก',
                'household_code' => 'HH-0014', 'head_name' => 'สมศรี ดอยงาม', 'phone' => '081-234-5614',
                'registered_at' => '2021-09-03', 'status' => 'active',
                'farm_code' => 'FARM-0014', 'farm_name' => 'สวนภูผาหมอกป้าสมศรี', 'total_area_rai' => 9,
                'gps_lat' => 14.5850, 'gps_lng' => 104.6850,
                'water_source' => 'สระน้ำในสวน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0021', 'area_rai' => 9, 'tree_count' => 72, 'variety' => 'หมอนทอง', 'planting_year' => 2016, 'irrigation' => 'ระบบน้ำอัตโนมัติ'],
                ],
            ],
            [
                'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย',
                'household_code' => 'HH-0015', 'head_name' => 'ประเสริฐ ศรีรัตนะ', 'phone' => '081-234-5615',
                'registered_at' => '2020-03-20', 'status' => 'active',
                'farm_code' => 'FARM-0015', 'farm_name' => 'สวนลุงประเสริฐ พิงพวย', 'total_area_rai' => 6,
                'gps_lat' => 14.7700, 'gps_lng' => 104.4550,
                'water_source' => 'น้ำประปาหมู่บ้าน', 'farm_status' => 'active',
                'plots' => [
                    ['code' => 'PLOT-0022', 'area_rai' => 6, 'tree_count' => 50, 'variety' => 'ชะนี', 'planting_year' => 2018, 'irrigation' => 'น้ำหยด'],
                ],
            ],
        ];

        // Convert the stable 15-record scenario dataset to the actual project
        // geography. This keeps downstream baseline/activity/impact seeders
        // useful while ensuring no demo household remains in the former
        // Chanthaburi, Rayong or Chumphon pilot areas.
        $sisaketAreas = [
            ['group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง', 'district' => 'ขุนหาญ', 'lat' => 14.6050, 'lng' => 104.3980],
            ['group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก', 'district' => 'กันทรลักษ์', 'lat' => 14.5850, 'lng' => 104.6850],
            ['group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย', 'district' => 'ศรีรัตนะ', 'lat' => 14.7700, 'lng' => 104.4550],
        ];

        foreach ($households as $index => $row) {
            $area = $sisaketAreas[$index % count($sisaketAreas)];
            $row['group'] = $area['group'];
            $row['farm_name'] = 'สวนทุเรียนภูเขาไฟ '.$area['district'].' '.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $row['gps_lat'] = $area['lat'] + (($index % 5) * 0.002);
            $row['gps_lng'] = $area['lng'] + (($index % 4) * 0.002);

            $group = FarmerGroup::with('tambon.district.province')->where('name', $row['group'])->first();
            $village = $group?->tambon?->villages()->first();
            $tambon = $group?->tambon;

            $household = Household::updateOrCreate(
                ['household_code' => $row['household_code']],
                [
                    'head_name' => $row['head_name'],
                    'phone' => $row['phone'],
                    'farmer_group_id' => $group?->id,
                    'village_id' => $village?->id,
                    'registered_at' => $row['registered_at'],
                    'status' => $row['status'],
                ]
            );

            $farm = $household->farms()->updateOrCreate(
                ['farm_code' => $row['farm_code']],
                [
                    'farm_name' => $row['farm_name'],
                    // Same tambon/district/province as the household's farmer
                    // group - realistic since a farm is normally worked by
                    // the household that lives right there (Task #43).
                    'province_id' => $tambon?->district?->province_id,
                    'district_id' => $tambon?->district_id,
                    'tambon_id' => $tambon?->id,
                    'total_area_rai' => $row['total_area_rai'],
                    'gps_lat' => $row['gps_lat'],
                    'gps_lng' => $row['gps_lng'],
                    'water_source' => $row['water_source'],
                    'status' => $row['farm_status'],
                ]
            );

            foreach ($row['plots'] as $plotRow) {
                $farm->plots()->updateOrCreate(
                    ['plot_code' => $plotRow['code']],
                    [
                        'area_rai' => $plotRow['area_rai'],
                        'tree_count' => $plotRow['tree_count'],
                        'durian_variety_id' => $varieties[$plotRow['variety']] ?? null,
                        'planting_year' => $plotRow['planting_year'],
                        'irrigation_type' => $plotRow['irrigation'],
                        'status' => $row['farm_status'],
                    ]
                );
            }
        }
    }
}
