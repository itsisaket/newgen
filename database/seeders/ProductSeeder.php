<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * F04/Inventory master reference data (Blueprint Appendix C.4 `products`)
 * - the finished biomass products the Inventory Ledger (8.3) and Kiln
 * Production Log (8.1/8.2) track.
 *
 * Same substance names as MaterialSeeder's biomass_product rows, but this
 * is intentionally a separate master list: `materials` are INPUTS bought/
 * used in F13 farm activities, `products` are OUTPUTS produced by F03
 * kiln batches and tracked through the Inventory Ledger.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'น้ำส้มควันไม้', 'unit' => 'ลิตร'],
            ['name' => 'ไบโอชาร์', 'unit' => 'กก.'],
            ['name' => 'ถ่านชาร์จ', 'unit' => 'กก.'],
            ['name' => 'ถ่านกัมมันต์', 'unit' => 'กก.'],
        ];

        foreach ($products as $row) {
            Product::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
