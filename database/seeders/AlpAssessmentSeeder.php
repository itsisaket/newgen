<?php

namespace Database\Seeders;

use App\Models\AlpAssessment;
use App\Models\Household;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * F07 - demo ALP history for the same households KilnBatchSeeder already
 * allocated technology to, so the level shown loosely tracks how much
 * they've actually used it (approved+verified batches -> higher level).
 * Must run after DemoFarmSeeder/DemoUserSeeder/TechnologySeeder/
 * KilnBatchSeeder.
 */
class AlpAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $assessor = User::where('email', 'field.officer1@drfis.local')->first()
            ?? User::where('email', 'admin@drfis.local')->first();

        if (! $assessor) {
            return;
        }

        $biocharKiln = Technology::where('name', 'เตาผลิตถ่านชีวภาพ')->first();
        $vinegarTank = Technology::where('name', 'ถังกลั่นน้ำส้มควันไม้')->first();

        // household_code => [technology, level]
        $rows = [
            'HH-0001' => [$biocharKiln, 4], // ปรับใช้/แก้ปัญหา - 2 batches incl. 1 approved+sold
            'HH-0003' => [$biocharKiln, 3], // ใช้ได้เอง - 1 approved batch
            'HH-0005' => [$vinegarTank, 2], // ทดลอง - only a draft batch so far
        ];

        foreach ($rows as $code => [$technology, $level]) {
            $household = Household::where('household_code', $code)->first();

            if (! $household || ! $technology) {
                continue;
            }

            AlpAssessment::firstOrCreate(
                ['household_id' => $household->id, 'technology_id' => $technology->id, 'assessment_date' => '2026-08-25'],
                [
                    'alp_level' => $level,
                    'assessor_id' => $assessor->id,
                    'notes' => 'ข้อมูลตัวอย่างสำหรับสาธิตระบบ',
                    'recorded_by' => $assessor->id,
                ]
            );
        }
    }
}
