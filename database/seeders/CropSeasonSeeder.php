<?php

namespace Database\Seeders;

use App\Models\CropSeason;
use Illuminate\Database\Seeder;

class CropSeasonSeeder extends Seeder
{
    public function run(): void
    {
        CropSeason::firstOrCreate(
            ['name' => 'ฤดูผลิต 2568'],
            ['start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'status' => 'closed']
        );

        CropSeason::firstOrCreate(
            ['name' => 'ฤดูผลิต 2569'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'active']
        );
    }
}
