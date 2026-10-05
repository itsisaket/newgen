<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Support\FarmerAccountProvisioner;
use Illuminate\Database\Seeder;

/**
 * Backfills a Farmer-role login account for every household that doesn't
 * have one yet (1 ครัวเรือน = 1 เกษตรกร - see
 * App\Support\FarmerAccountProvisioner). Needed because HH-0001..HH-0012
 * from DemoFarmSeeder were created before this feature existed. Safe to
 * re-run: provision() is a no-op for a household that already has
 * user_id set, so this never creates a second account.
 *
 * Prints the generated credentials to the console since seeder output is
 * the only place a demo password like this is ever shown - real
 * households get theirs from the one-time flash message in the app
 * instead (households.reset-farmer-password / HouseholdController::store()).
 */
class FarmerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        foreach (Household::whereNull('user_id')->orderBy('household_code')->get() as $household) {
            $credentials = FarmerAccountProvisioner::provision($household);

            if ($credentials) {
                $rows[] = [$household->household_code, $household->head_name, $credentials['email'], $credentials['password']];
            }
        }

        if ($rows && $this->command) {
            $this->command->info('สร้างบัญชีเกษตรกร (Farmer login) ใหม่ '.count($rows).' บัญชี:');
            $this->command->table(['รหัสครัวเรือน', 'หัวหน้าครัวเรือน', 'อีเมล', 'รหัสผ่าน (ชั่วคราว)'], $rows);
        }
    }
}
