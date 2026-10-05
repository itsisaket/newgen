<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\CropSeason;
use App\Models\FarmActivity;
use App\Models\Material;
use App\Models\Plot;
use App\Models\User;
use App\Support\WorkflowStatus;
use Illuminate\Database\Seeder;

/**
 * F13 - Farm Activity Log demo data (Blueprint section 7.2), two records
 * per plot seeded by DemoFarmSeeder (one in the current season, one in the
 * previous season so the "Before-After" cost comparison Blueprint 7.2
 * describes has something to compare), cycling through a handful of
 * realistic activity/material combinations and every workflow status.
 *
 * Same seeding-vs-WorkflowService note as HouseholdBaselineSeeder: status
 * is written directly here as this fixture's starting state, not as a
 * live transition, so no audit_log rows are created for these rows.
 */
class FarmActivitySeeder extends Seeder
{
    public function run(): void
    {
        $seasonCurrent = CropSeason::where('name', 'ฤดูผลิต 2569')->first();
        $seasonPrevious = CropSeason::where('name', 'ฤดูผลิต 2568')->first();

        if (! $seasonCurrent || ! $seasonPrevious) {
            return;
        }

        $officer1 = User::where('email', 'field.officer1@drfis.local')->first();
        $officer2 = User::where('email', 'field.officer2@drfis.local')->first();
        $fallback = User::where('email', 'admin@drfis.local')->first();

        if (! $officer1 && ! $officer2 && ! $fallback) {
            return;
        }

        // [activity_type category, activity_type name, material name or
        // null, quantity, unit_cost, labor_hours, labor_cost]
        $templates = [
            ['fertilizer', 'ปุ๋ยอินทรีย์', 'ปุ๋ยอินทรีย์', 50, 15, 2, 600],
            ['fertilizer', 'ปุ๋ยเคมีสูตรเสมอ', 'ปุ๋ยเคมี 15-15-15', 30, 22, 1.5, 450],
            ['chemical', 'สารป้องกันเชื้อรา', 'สารป้องกันเชื้อรา', 2, 350, 1, 250],
            ['labor', 'ตัดแต่งกิ่ง', null, null, null, 4, 1200],
            ['biomass_product', 'ใช้น้ำส้มควันไม้', 'น้ำส้มควันไม้', 5, 40, 1, 250],
            ['harvest_transport', 'ค่าแรงเก็บเกี่ยว', null, null, null, 6, 2100],
        ];

        $statuses = [
            WorkflowStatus::DRAFT,
            WorkflowStatus::SUBMITTED,
            WorkflowStatus::VERIFIED,
            WorkflowStatus::APPROVED,
        ];

        $plots = Plot::orderBy('plot_code')->get();
        $i = 0;

        foreach ($plots as $plot) {
            $recordedBy = ($i % 2 === 0 ? $officer1 : $officer2) ?? $fallback;

            if (! $recordedBy) {
                $i++;

                continue;
            }

            $templateA = $templates[$i % count($templates)];
            $templateB = $templates[($i + 3) % count($templates)];

            $month = ($i % 9) + 1;

            $this->createActivity($plot, $seasonCurrent, $templateA, $recordedBy,
                sprintf('2026-%02d-10', $month), $statuses[$i % count($statuses)]);

            $this->createActivity($plot, $seasonPrevious, $templateB, $recordedBy,
                sprintf('2025-%02d-20', $month), $statuses[($i + 2) % count($statuses)]);

            $i++;
        }
    }

    /**
     * @param  array{0: string, 1: string, 2: ?string, 3: ?float, 4: ?float, 5: float, 6: float}  $template
     */
    private function createActivity(Plot $plot, CropSeason $season, array $template, User $recordedBy, string $date, string $status): void
    {
        [$category, $activityName, $materialName, $quantity, $unitCost, $laborHours, $laborCost] = $template;

        $activityType = ActivityType::where('category', $category)->where('name', $activityName)->first();
        $material = $materialName ? Material::where('name', $materialName)->first() : null;

        if (! $activityType) {
            return;
        }

        $totalCost = (float) ($quantity ?? 0) * (float) ($unitCost ?? 0) + (float) $laborCost;

        FarmActivity::firstOrCreate(
            [
                'plot_id' => $plot->id,
                'activity_type_id' => $activityType->id,
                'activity_date' => $date,
            ],
            [
                'crop_season_id' => $season->id,
                'material_id' => $material?->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'labor_hours' => $laborHours,
                'labor_cost' => $laborCost,
                'total_cost' => $totalCost,
                'status' => $status,
                'recorded_by' => $recordedBy->id,
            ]
        );
    }
}
