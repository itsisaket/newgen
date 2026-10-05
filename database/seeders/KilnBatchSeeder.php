<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\KilnBatch;
use App\Models\Product;
use App\Models\Technology;
use App\Models\TechnologyAsset;
use App\Models\TechnologyAssignment;
use App\Models\User;
use App\Services\InventoryLedgerService;
use App\Support\WorkflowStatus;
use Illuminate\Database\Seeder;

/**
 * Demo F12 allocation + F03 kiln production history, so the households/
 * show "เทคโนโลยี ชีวมวล และสต็อก" card and the kiln-batches/inventory-
 * transactions screens have real, non-zero numbers out of the box - same
 * "seed a realistic slice, don't seed everything" approach as
 * FarmActivitySeeder/InnovatorEvaluationSeeder.
 *
 * Must run after TechnologySeeder (needs technology_assets), ProductSeeder
 * (needs products), DemoFarmSeeder (needs the demo households), and
 * DemoUserSeeder (needs innovator@drfis.local/field.officer1@drfis.local
 * as operator/recorded_by).
 *
 * Like FarmActivitySeeder, kiln_batches.status is written directly here
 * as each row's STARTING state (not a live WorkflowService transition),
 * so no audit_log rows are created for these seeded rows. But for the
 * batch seeded as 'approved', the resulting inventory_transactions ARE
 * written through InventoryLedgerService - never a direct ::create() -
 * mirroring exactly what KilnBatchController::approve() does for a real
 * approval, so the ledger's balance_after chain stays provably correct
 * even for seed data.
 */
class KilnBatchSeeder extends Seeder
{
    public function run(): void
    {
        $ledger = app(InventoryLedgerService::class);

        $fallback = User::where('email', 'admin@drfis.local')->first();
        $operator = User::where('email', 'innovator@drfis.local')->first() ?? $fallback;
        $recordedBy = User::where('email', 'field.officer1@drfis.local')->first() ?? $fallback;

        if (! $operator || ! $recordedBy) {
            return;
        }

        $biocharKiln = Technology::where('name', 'เตาผลิตถ่านชีวภาพ')->first();
        $vinegarTank = Technology::where('name', 'ถังกลั่นน้ำส้มควันไม้')->first();
        $biochar = Product::where('name', 'ไบโอชาร์')->first();
        $vinegar = Product::where('name', 'น้ำส้มควันไม้')->first();
        $charcoal = Product::where('name', 'ถ่านชาร์จ')->first();

        if (! $biocharKiln || ! $vinegarTank || ! $biochar || ! $vinegar || ! $charcoal) {
            return;
        }

        $biocharKilnAssets = TechnologyAsset::where('technology_id', $biocharKiln->id)->orderBy('id')->get();
        $vinegarTankAssets = TechnologyAsset::where('technology_id', $vinegarTank->id)->orderBy('id')->get();

        // household_code => [technology asset to allocate, output product(s),
        // status for the SECOND (later) batch - the first is always an
        // older APPROVED batch so every demo household has ledger history].
        $plan = [
            ['code' => 'HH-0001', 'asset' => $biocharKilnAssets->get(0), 'outputs' => [$biochar, $vinegar], 'laterStatus' => WorkflowStatus::VERIFIED],
            ['code' => 'HH-0003', 'asset' => $biocharKilnAssets->get(1), 'outputs' => [$charcoal], 'laterStatus' => WorkflowStatus::SUBMITTED],
            ['code' => 'HH-0005', 'asset' => $vinegarTankAssets->get(0), 'outputs' => [$vinegar], 'laterStatus' => WorkflowStatus::DRAFT],
        ];

        foreach ($plan as $i => $row) {
            $household = Household::where('household_code', $row['code'])->first();

            if (! $household || ! $row['asset']) {
                continue;
            }

            TechnologyAssignment::firstOrCreate(
                ['technology_asset_id' => $row['asset']->id, 'household_id' => $household->id],
                ['assigned_date' => '2026-06-01', 'status' => 'active', 'assigned_by' => $recordedBy->id]
            );

            $n = $i + 1;

            $this->createBatch(
                $ledger, $household, $row['asset'], $operator, $recordedBy,
                batchCode: sprintf('KILN-%04d', ($n * 2) - 1),
                date: '2026-07-10',
                status: WorkflowStatus::APPROVED,
                products: $row['outputs'],
            );

            $this->createBatch(
                $ledger, $household, $row['asset'], $operator, $recordedBy,
                batchCode: sprintf('KILN-%04d', $n * 2),
                date: '2026-08-20',
                status: $row['laterStatus'],
                products: $row['outputs'],
            );
        }
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function createBatch(
        InventoryLedgerService $ledger,
        Household $household,
        TechnologyAsset $asset,
        User $operator,
        User $recordedBy,
        string $batchCode,
        string $date,
        string $status,
        array $products,
    ): void {
        $batch = KilnBatch::firstOrCreate(
            ['batch_code' => $batchCode],
            [
                'technology_asset_id' => $asset->id,
                'household_id' => $household->id,
                'operator_id' => $operator->id,
                'batch_date' => $date,
                'biomass_input_kg' => 150,
                'labor_cost' => 600,
                'energy_cost' => 150,
                'other_cost' => 50,
                'production_time_hours' => 8,
                'status' => $status,
                'recorded_by' => $recordedBy->id,
            ]
        );

        if (! $batch->wasRecentlyCreated) {
            return;
        }

        $quantities = [35, 12];

        foreach (array_values($products) as $idx => $product) {
            $batch->outputs()->create([
                'product_id' => $product->id,
                'output_quantity' => $quantities[$idx] ?? 10,
                'unit' => $product->unit,
                'quality_grade' => 'A',
            ]);
        }

        if ($status !== WorkflowStatus::APPROVED) {
            return;
        }

        $batch->load('outputs.product');

        foreach ($batch->outputs as $output) {
            $ledger->record(
                product: $output->product,
                householdId: $household->id,
                type: 'production',
                quantity: (float) $output->output_quantity,
                transactionDate: $date,
                recordedBy: $recordedBy,
                reference: $batch,
            );
        }
    }
}
