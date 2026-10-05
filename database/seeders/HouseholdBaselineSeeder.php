<?php

namespace Database\Seeders;

use App\Models\CropSeason;
use App\Models\Household;
use App\Models\HouseholdBaseline;
use App\Models\User;
use App\Support\WorkflowStatus;
use Illuminate\Database\Seeder;

/**
 * F01 - Household Baseline demo data (Blueprint section 6), one record per
 * household seeded by DemoFarmSeeder, spread across every workflow status
 * (Blueprint 16.1) so the status badge and the M01-style list screen both
 * show a realistic mix instead of everything sitting in "draft".
 *
 * Note: this writes `status` directly instead of going through
 * WorkflowService. That service's rule ("never set $model->status
 * directly - always go through the service") is about real transitions
 * made by the app; a seeder establishing a fixture's *starting* state is
 * the one accepted exception, same as e.g. Laravel's own migrations
 * seeding initial data. No audit_log rows are written for these fixtures
 * for the same reason.
 */
class HouseholdBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $season = CropSeason::where('name', 'ฤดูผลิต 2569')->first();

        if (! $season) {
            return;
        }

        $officer1 = User::where('email', 'field.officer1@drfis.local')->first();
        $officer2 = User::where('email', 'field.officer2@drfis.local')->first();
        $fallback = User::where('email', 'admin@drfis.local')->first();

        // household_code => [status, total_cost, total_income]
        $rows = [
            'HH-0001' => [WorkflowStatus::DRAFT, 42000, 95000],
            'HH-0002' => [WorkflowStatus::DRAFT, 38500, 72000],
            'HH-0003' => [WorkflowStatus::DRAFT, 61000, 140000],
            'HH-0004' => [WorkflowStatus::DRAFT, 29500, 58000],
            'HH-0005' => [WorkflowStatus::SUBMITTED, 47000, 101000],
            'HH-0006' => [WorkflowStatus::SUBMITTED, 33000, 66000],
            'HH-0007' => [WorkflowStatus::SUBMITTED, 78000, 175000],
            'HH-0008' => [WorkflowStatus::VERIFIED, 36500, 80000],
            'HH-0009' => [WorkflowStatus::VERIFIED, 45000, 92000],
            'HH-0010' => [WorkflowStatus::APPROVED, 31000, 64000],
            'HH-0011' => [WorkflowStatus::APPROVED, 55000, 118000],
            'HH-0012' => [WorkflowStatus::REVISION_REQUESTED, 40000, 85000],
        ];

        $i = 0;

        foreach ($rows as $householdCode => [$status, $cost, $income]) {
            $household = Household::where('household_code', $householdCode)->first();

            if (! $household) {
                continue;
            }

            $recordedBy = ($i % 2 === 0 ? $officer1 : $officer2) ?? $fallback;
            $i++;

            if (! $recordedBy) {
                continue;
            }

            HouseholdBaseline::firstOrCreate(
                ['household_id' => $household->id, 'crop_season_id' => $season->id],
                [
                    'total_cost' => $cost,
                    'total_income' => $income,
                    'management_practice_notes' => 'ข้อมูลตัวอย่างสำหรับสาธิตระบบ - ใช้ปุ๋ยอินทรีย์ร่วมกับปุ๋ยเคมี รดน้ำตามระบบชลประทานของกลุ่ม',
                    'status' => $status,
                    'recorded_by' => $recordedBy->id,
                ]
            );
        }
    }
}
