<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\MarketValidation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * F08 full - demo buyer-declared demand (Blueprint 10.3), distinct from
 * the actual Sale rows SaleSeeder writes. Must run after BuyerSeeder/
 * DemoUserSeeder.
 */
class MarketValidationSeeder extends Seeder
{
    public function run(): void
    {
        $recordedBy = User::where('email', 'field.officer1@drfis.local')->first()
            ?? User::where('email', 'admin@drfis.local')->first();

        if (! $recordedBy) {
            return;
        }

        $rows = [
            [
                'buyer' => 'ล้งทุเรียนศรีสะเกษ', 'product_type' => 'durian',
                'demand_quantity' => 50000, 'price' => 95, 'frequency' => 'ตามฤดูกาล',
                'has_loi_mou' => true, 'valid_from' => '2026-06-01', 'valid_to' => '2026-09-30',
            ],
            [
                'buyer' => 'บริษัท ไบโอชาร์ไทย จำกัด', 'product_type' => 'bioproduct',
                'demand_quantity' => 2000, 'price' => 45, 'frequency' => 'รายเดือน',
                'has_loi_mou' => false, 'valid_from' => '2026-07-01', 'valid_to' => null,
            ],
        ];

        foreach ($rows as $row) {
            $buyer = Buyer::where('name', $row['buyer'])->first();

            if (! $buyer) {
                continue;
            }

            MarketValidation::firstOrCreate(
                ['buyer_id' => $buyer->id, 'product_type' => $row['product_type'], 'valid_from' => $row['valid_from']],
                [
                    'demand_quantity' => $row['demand_quantity'],
                    'price' => $row['price'],
                    'frequency' => $row['frequency'],
                    'has_loi_mou' => $row['has_loi_mou'],
                    'valid_to' => $row['valid_to'],
                    'recorded_by' => $recordedBy->id,
                ]
            );
        }
    }
}
