<?php

namespace Database\Seeders;

use App\Exceptions\InventoryLedgerException;
use App\Models\Buyer;
use App\Models\CropSeason;
use App\Models\Household;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\InventoryLedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * F08 full - demo actual sales (Blueprint ค.5), the revenue source
 * EconomicImpactSeeder reads from. Durian sales are plain revenue rows;
 * bioproduct sales go through InventoryLedgerService like
 * KilnBatchSeeder's approved-batch production, so the seeded ledger
 * balance and the seeded sales revenue can never disagree - same
 * discipline SaleController::store() enforces for real users.
 *
 * Must run after DemoFarmSeeder/DemoUserSeeder/CropSeasonSeeder/
 * BuyerSeeder/ProductSeeder AND KilnBatchSeeder (bioproduct sales here
 * only work because that seeder already put stock in the ledger for
 * HH-0001/0003/0005's approved batches).
 */
class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $season = CropSeason::where('name', 'ฤดูผลิต 2569')->first();
        $recordedBy = User::where('email', 'field.officer1@drfis.local')->first()
            ?? User::where('email', 'admin@drfis.local')->first();

        if (! $season || ! $recordedBy) {
            return;
        }

        $lng = Buyer::where('name', 'ล้งทุเรียนศรีสะเกษ')->first();
        $biocharBuyer = Buyer::where('name', 'บริษัท ไบโอชาร์ไทย จำกัด')->first();
        $marketBuyer = Buyer::where('name', 'ตลาดสดเมืองศรีสะเกษ (ผู้บริโภคตรง)')->first();

        // Durian sales - the households that also passed the F09-lite
        // evaluation (InnovatorEvaluationSeeder), so F06's Durian Income
        // Increase has something real to compare against their F01
        // Baseline income.
        $durianRows = [
            ['HH-0001', 1200, 90],
            ['HH-0003', 2500, 95],
            ['HH-0005', 900, 85],
            ['HH-0007', 3000, 100],
            ['HH-0011', 1800, 92],
        ];

        foreach ($durianRows as [$code, $qty, $price]) {
            $household = Household::where('household_code', $code)->first();

            if (! $household) {
                continue;
            }

            Sale::firstOrCreate(
                [
                    'seller_household_id' => $household->id,
                    'crop_season_id' => $season->id,
                    'product_type' => 'durian',
                    'sale_date' => '2026-07-25',
                ],
                [
                    'buyer_id' => $lng?->id,
                    'product_id' => null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => round($qty * $price, 2),
                    'recorded_by' => $recordedBy->id,
                ]
            );
        }

        // Bioproduct sales - only a slice of each household's approved
        // KilnBatchSeeder stock, so the "คงเหลือ" balance shown on the
        // household page stays non-zero too.
        $ledger = app(InventoryLedgerService::class);

        $bioRows = [
            ['HH-0001', $biocharBuyer, 'ไบโอชาร์', 10, 45],
            ['HH-0003', $marketBuyer, 'ถ่านชาร์จ', 15, 25],
            ['HH-0005', $biocharBuyer, 'น้ำส้มควันไม้', 10, 60],
        ];

        foreach ($bioRows as [$code, $buyer, $productName, $qty, $price]) {
            $household = Household::where('household_code', $code)->first();
            $product = Product::where('name', $productName)->first();

            if (! $household || ! $product) {
                continue;
            }

            $alreadySold = Sale::where('seller_household_id', $household->id)
                ->where('product_id', $product->id)
                ->where('sale_date', '2026-08-25')
                ->exists();

            if ($alreadySold) {
                continue;
            }

            try {
                DB::transaction(function () use ($ledger, $household, $buyer, $product, $qty, $price, $season, $recordedBy) {
                    $sale = Sale::create([
                        'seller_household_id' => $household->id,
                        'buyer_id' => $buyer?->id,
                        'product_type' => 'bioproduct',
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_amount' => round($qty * $price, 2),
                        'sale_date' => '2026-08-25',
                        'crop_season_id' => $season->id,
                        'recorded_by' => $recordedBy->id,
                    ]);

                    $ledger->record(
                        product: $product,
                        householdId: $household->id,
                        type: 'sale',
                        quantity: (float) $qty,
                        transactionDate: '2026-08-25',
                        recordedBy: $recordedBy,
                        reference: $sale,
                        buyerId: $buyer?->id,
                    );
                });
            } catch (InventoryLedgerException) {
                // Not enough seeded stock in this environment - skip
                // gracefully rather than aborting the whole seeder.
            }
        }
    }
}
