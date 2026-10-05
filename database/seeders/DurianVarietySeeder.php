<?php

namespace Database\Seeders;

use App\Models\DurianVariety;
use Illuminate\Database\Seeder;

class DurianVarietySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['หมอนทอง', 'ชะนี', 'ก้านยาว', 'กระดุม', 'พวงมณี'] as $name) {
            DurianVariety::firstOrCreate(['name' => $name]);
        }
    }
}
