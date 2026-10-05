<?php

namespace Database\Seeders;

use App\Models\Buyer;
use Illuminate\Database\Seeder;

/**
 * F08 full - master buyer list (Blueprint Appendix C.5). Previously
 * (F14/F08-lite round) this table had no seeder at all, so
 * harvest-records.create's buyer dropdown was empty out of the box -
 * this gives every F08 screen (buyers/market-validations/sales) and the
 * harvest-records form real rows to point at, using firstOrCreate() so
 * `db:seed` stays safe to re-run.
 */
class BuyerSeeder extends Seeder
{
    public function run(): void
    {
        $buyers = [
            ['name' => 'ล้งทุเรียนศรีสะเกษ', 'buyer_type' => 'ล้ง', 'phone' => '045-611111', 'contact' => 'Line: @srisaketdurian', 'standard_required' => 'GAP'],
            ['name' => 'สหกรณ์การเกษตรกันทรลักษ์', 'buyer_type' => 'สหกรณ์', 'phone' => '045-622222', 'contact' => null, 'standard_required' => null],
            ['name' => 'บริษัท ไบโอชาร์ไทย จำกัด', 'buyer_type' => 'ผู้รับซื้อผลิตภัณฑ์ชีวมวล', 'phone' => '02-1234567', 'contact' => 'biochar.thai@example.com', 'standard_required' => 'มาตรฐานถ่านชีวภาพอุตสาหกรรม'],
            ['name' => 'ตลาดสดเมืองศรีสะเกษ (ผู้บริโภคตรง)', 'buyer_type' => 'ผู้บริโภคตรง', 'phone' => null, 'contact' => null, 'standard_required' => null],
        ];

        foreach ($buyers as $data) {
            Buyer::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
