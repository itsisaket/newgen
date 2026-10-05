<?php

namespace Database\Seeders;

use App\Models\Technology;
use App\Models\TechnologyAsset;
use Illuminate\Database\Seeder;

/**
 * F12 master reference data (Blueprint section 8.1 / Appendix C.4
 * `technologies`/`technology_assets`) - technology TYPES plus a couple of
 * physical units under each, so the technologies/technology-assets
 * screens and the kiln-batch "เครื่องที่มีอยู่" list have real examples.
 * Allocation to households is seeded separately by KilnBatchSeeder (needs
 * these assets to already exist).
 */
class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            ['name' => 'เตาผลิตถ่านชีวภาพ', 'type' => 'kiln', 'description' => 'เตาเผาถ่านชีวภาพ (Biochar) แบบดัดแปลงถังน้ำมัน 200 ลิตร'],
            ['name' => 'ถังกลั่นน้ำส้มควันไม้', 'type' => 'distillation', 'description' => 'ถังกลั่นควบแน่นน้ำส้มควันไม้จากควันเตาเผาถ่าน'],
            ['name' => 'เครื่องบดย่อยชีวมวล', 'type' => 'processing', 'description' => 'เครื่องบดย่อยกิ่งไม้/เศษวัสดุก่อนเข้ากระบวนการผลิต'],
        ];

        foreach ($technologies as $row) {
            $technology = Technology::firstOrCreate(['name' => $row['name']], $row);

            // Two physical units per type - asset codes are derived from
            // the technology's own id, so re-running `db:seed` converges
            // to the same codes instead of piling up duplicates.
            for ($i = 1; $i <= 2; $i++) {
                TechnologyAsset::firstOrCreate(
                    ['asset_code' => sprintf('TECH-%02d-%02d', $technology->id, $i)],
                    [
                        'technology_id' => $technology->id,
                        'acquired_date' => '2025-06-01',
                        'condition_status' => 'good',
                    ]
                );
            }
        }
    }
}
