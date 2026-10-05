<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Sprint 1-2 seed order: roles/permissions and locations must exist
     * before the admin user and demo farm are assigned/linked to them.
     * DemoUserSeeder needs AdminUserSeeder's Super Admin (as created_by
     * for area assignments); HouseholdBaselineSeeder/FarmActivitySeeder
     * need both the demo households/farms/plots AND the demo users
     * (as recorded_by) to already exist, so those two run last.
     *
     * FarmerAccountSeeder needs the demo households (after DemoFarmSeeder)
     * and the Farmer role (after RolePermissionSeeder). InnovatorEvaluationSeeder
     * needs households already linked to a Farmer login (after
     * FarmerAccountSeeder) and evaluator@drfis.local to exist (after
     * DemoUserSeeder) - both run last, after everything else.
     *
     * Sprint 3 (Technology & Biomass): TechnologySeeder/ProductSeeder are
     * master reference data like MaterialSeeder, so they run alongside it
     * early on. KilnBatchSeeder allocates those assets to demo households
     * and writes demo kiln batches/inventory transactions, so it needs
     * TechnologySeeder+ProductSeeder (master data), DemoFarmSeeder (the
     * households) and DemoUserSeeder (operator/recorded_by users) to have
     * already run - placed at the very end alongside InnovatorEvaluationSeeder
     * for the same reason.
     *
     * Sprint 4 (Impact & Innovator, F06/F07/F08/F09 full): BuyerSeeder and
     * CompetencyIndicatorSeeder are master data like TechnologySeeder, so
     * they run alongside it. AlpAssessmentSeeder/MarketValidationSeeder/
     * SaleSeeder/CompetencyAssessmentSeeder/EconomicImpactSeeder all run at
     * the very end: AlpAssessmentSeeder needs KilnBatchSeeder's technology
     * assignments, SaleSeeder needs KilnBatchSeeder's approved-batch
     * ledger stock (for bioproduct sales) and BuyerSeeder, CompetencyAssessmentSeeder
     * needs InnovatorEvaluationSeeder's `innovators` rows, and
     * EconomicImpactSeeder needs HouseholdBaselineSeeder + SaleSeeder +
     * KilnBatchSeeder all already written - see each seeder's own
     * doc-comment.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            LocationSeeder::class,
            CropSeasonSeeder::class,
            DurianVarietySeeder::class,
            ActivityTypeSeeder::class,
            MaterialSeeder::class,
            TechnologySeeder::class,
            ProductSeeder::class,
            BuyerSeeder::class,
            CompetencyIndicatorSeeder::class,
            DemoFarmSeeder::class,
            AdminUserSeeder::class,
            DemoUserSeeder::class,
            HouseholdBaselineSeeder::class,
            FarmActivitySeeder::class,
            FarmerAccountSeeder::class,
            InnovatorEvaluationSeeder::class,
            KilnBatchSeeder::class,
            AlpAssessmentSeeder::class,
            MarketValidationSeeder::class,
            SaleSeeder::class,
            CompetencyAssessmentSeeder::class,
            EconomicImpactSeeder::class,
        ]);
    }
}
