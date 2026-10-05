<?php

namespace Database\Seeders;

use App\Models\CropSeason;
use App\Models\Household;
use App\Services\EconomicImpactService;
use Illuminate\Database\Seeder;

/**
 * F06 - demo economic_impacts snapshots (Blueprint 11), computed the
 * SAME way EconomicImpactController does (never inserted directly - see
 * the economic_impacts migration's doc-comment) so the seeded numbers are
 * provably consistent with the Baseline/Sales/KilnBatch data the other
 * Sprint 4 seeders just wrote. Must run last - after HouseholdBaselineSeeder,
 * KilnBatchSeeder and SaleSeeder.
 */
class EconomicImpactSeeder extends Seeder
{
    public function run(): void
    {
        $season = CropSeason::where('name', 'ฤดูผลิต 2569')->first();

        if (! $season) {
            return;
        }

        $service = app(EconomicImpactService::class);

        $codes = ['HH-0001', 'HH-0003', 'HH-0005', 'HH-0007', 'HH-0011'];

        foreach ($codes as $code) {
            $household = Household::where('household_code', $code)->first();

            if ($household) {
                $service->calculateForHousehold($household, $season);
            }
        }
    }
}
