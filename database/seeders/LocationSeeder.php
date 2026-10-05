<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\FarmerGroup;
use App\Models\Province;
use App\Models\Tambon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Full official Thailand จังหวัด/อำเภอ/ตำบล (province/district/sub-district)
 * master data - Blueprint Appendix B "Master Location: จังหวัด อำเภอ ตำบล
 * หมู่บ้าน". Replaces the original Sprint 1/2 pilot subset (3 provinces /
 * 6 districts / 12 tambons, just enough to cover the demo households'
 * own area) - every location picker in the app now covers the whole
 * country, not just the three pilot provinces.
 *
 * Source: kongvut/thai-province-data (MIT license), a maintained mirror
 * of the Dept. of Provincial Administration's official area codes -
 * id/name_th/name_en/province_id/district_id/zip_code all come straight
 * from there. Bundled as JSON under database/data/thailand/ (77
 * provinces / 930 districts / 7,452 sub-districts as of this snapshot)
 * rather than fetched at seed time, so `php artisan db:seed` still works
 * offline / when GitHub isn't reachable from the server. To refresh
 * later: re-download api/latest/province.json, district.json and
 * sub_district.json from https://github.com/kongvut/thai-province-data
 * and overwrite the three files in that folder (same field names).
 *
 * IMPORTANT - this seeder TRUNCATES and fully reinstalls provinces/
 * districts/tambons every run, because the official dataset's ids are
 * fixed area codes (e.g. Bangkok province id 1, เขตพระนคร district id
 * 1001) rather than arbitrary auto-increment values, so it can't be
 * merged in with firstOrCreate() the way the old pilot seeder did. Safe
 * pre-production against demo/dev data (same caveat the original pilot
 * seeder already carried) - villages/farmer_groups/households/farms that
 * pointed at the *old* pilot province/district/tambon ids are re-attached
 * to the newly installed real records below (by matching name, not id),
 * and DemoFarmSeeder/DemoUserSeeder (which run right after this seeder in
 * DatabaseSeeder) already look up Province/District/Tambon/FarmerGroup by
 * name_th rather than hardcoded id, so a normal `php artisan migrate` +
 * `php artisan db:seed` repairs everything in one pass. Do NOT run this
 * against a database that already has real (non-demo) household/farm
 * data entered by hand without reviewing first - back it up, since
 * farmer_group_id/village_id on those rows would otherwise be pointing at
 * ids that no longer exist until re-linked.
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $this->installOfficialLocations();
        $this->seedDemoVillageTree();
        $this->seedOfficialVillages();
    }

    /**
     * Truncates and bulk-inserts the full official province/district/
     * tambon dataset. FK checks are disabled around the truncate/insert
     * block (rather than deleting in dependency order) because inserting
     * with explicit official ids - instead of letting auto-increment
     * assign them - is the whole point here.
     */
    private function installOfficialLocations(): void
    {
        $base = database_path('data/thailand');

        $provinces = json_decode(file_get_contents($base.'/provinces.json'), true);
        $districts = json_decode(file_get_contents($base.'/districts.json'), true);
        $tambons = json_decode(file_get_contents($base.'/sub_districts.json'), true);

        Schema::disableForeignKeyConstraints();

        // Children first (villages/farmer_groups point at tambons, which
        // point at districts, which point at provinces) even though FK
        // checks are off, so nothing is left referencing a row that's
        // about to disappear for even a moment mid-run.
        DB::table('villages')->truncate();
        DB::table('farmer_groups')->truncate();
        DB::table('tambons')->truncate();
        DB::table('districts')->truncate();
        DB::table('provinces')->truncate();

        $now = now();

        collect($provinces)->chunk(500)->each(function ($chunk) use ($now) {
            DB::table('provinces')->insert(
                $chunk->map(fn ($p) => [
                    'id' => $p['id'],
                    'name_th' => $p['name_th'],
                    'name_en' => $p['name_en'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });

        collect($districts)->chunk(500)->each(function ($chunk) use ($now) {
            DB::table('districts')->insert(
                $chunk->map(fn ($d) => [
                    'id' => $d['id'],
                    'province_id' => $d['province_id'],
                    'name_th' => $d['name_th'],
                    'name_en' => $d['name_en'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });

        collect($tambons)->chunk(500)->each(function ($chunk) use ($now) {
            DB::table('tambons')->insert(
                $chunk->map(fn ($t) => [
                    'id' => $t['id'],
                    'district_id' => $t['district_id'],
                    'name_th' => $t['name_th'],
                    'name_en' => $t['name_en'],
                    'zip_code' => $t['zip_code'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Project-area village/farmer-group tree (Blueprint 3.1 hierarchy).
     * Demo data is deliberately limited to the three Sisaket volcanic-soil
     * durian districts: Khun Han, Kantharalak and Si Rattana. Thailand-wide
     * province/district/tambon rows remain available as master data, but no
     * demo household is attached outside these three districts.
     */
    private function seedDemoVillageTree(): void
    {
        $tree = [
            'ศรีสะเกษ' => [
                'ขุนหาญ' => [
                    'บักดอง' => ['village' => 'หมู่ 5 บ้านบักดอง', 'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูเขาไฟบักดอง'],
                ],
                'กันทรลักษ์' => [
                    'ภูผาหมอก' => ['village' => 'หมู่ 3 บ้านภูผาหมอก', 'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนภูผาหมอก'],
                ],
                'ศรีรัตนะ' => [
                    'พิงพวย' => ['village' => 'หมู่ 2 บ้านพิงพวย', 'group' => 'กลุ่มเกษตรกรผู้ปลูกทุเรียนพิงพวย'],
                ],
            ],
        ];

        foreach ($tree as $provinceName => $districtsTree) {
            $province = Province::where('name_th', $provinceName)->first();
            if (! $province) {
                continue;
            }

            foreach ($districtsTree as $districtName => $tambons) {
                $district = District::where('province_id', $province->id)
                    ->where('name_th', $districtName)
                    ->first();
                if (! $district) {
                    continue;
                }

                foreach ($tambons as $tambonName => $info) {
                    $tambon = Tambon::where('district_id', $district->id)
                        ->where('name_th', $tambonName)
                        ->first();
                    if (! $tambon) {
                        continue;
                    }

                    $tambon->villages()->firstOrCreate(['name_th' => $info['village']]);

                    FarmerGroup::firstOrCreate(
                        ['name' => $info['group']],
                        ['tambon_id' => $tambon->id, 'leader_name' => null]
                    );
                }
            }
        }
    }

    /**
     * Installs the compact DOPA village snapshots bundled under
     * database/data/thailand/villages. This runs after the three demo
     * villages so their household foreign keys keep the same rows; matching
     * records receive their official code instead of being duplicated.
     */
    private function seedOfficialVillages(): void
    {
        $files = glob(database_path('data/thailand/villages/*.json')) ?: [];
        if ($files === []) {
            return;
        }

        $existing = DB::table('villages')->get()->keyBy(function ($village) {
            $name = preg_replace('/^หมู่\s*\d+\s*/u', '', $village->name_th);
            $name = preg_replace('/^บ้าน/u', '', $name);

            return $village->tambon_id.'|'.str_replace(' ', '', $name);
        });
        $tambonIds = DB::table('tambons')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $districtIds = DB::table('districts')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $provinceIds = DB::table('provinces')->pluck('id', 'name_th')->all();
        $now = now();

        foreach ($files as $file) {
            $rows = json_decode(file_get_contents($file), true) ?: [];
            $upserts = [];

            foreach ($rows as $item) {
                $tambonId = (int) substr((string) ($item['tcode'] ?? ''), 0, 6);
                $name = trim((string) ($item['mname'] ?? ''));
                $code = (string) ($item['mcode'] ?? '');
                if (strlen($code) !== 8 || $name === '') {
                    continue;
                }

                if (! isset($tambonIds[$tambonId])) {
                    $districtId = (int) ($item['acode'] ?? 0);
                    $provinceId = $provinceIds[trim((string) ($item['pname'] ?? ''))] ?? null;
                    if (! $districtId || ! $provinceId) {
                        continue;
                    }

                    if (! isset($districtIds[$districtId])) {
                        DB::table('districts')->insertOrIgnore([
                            'id' => $districtId,
                            'province_id' => $provinceId,
                            'name_th' => trim((string) ($item['aname'] ?? '')),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $districtIds[$districtId] = true;
                    }

                    DB::table('tambons')->insertOrIgnore([
                        'id' => $tambonId,
                        'district_id' => $districtId,
                        'name_th' => trim((string) ($item['tname'] ?? '')),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $tambonIds[$tambonId] = true;
                }

                $key = $tambonId.'|'.str_replace(' ', '', preg_replace('/^บ้าน/u', '', $name));
                if ($manual = $existing->get($key)) {
                    DB::table('villages')->where('id', $manual->id)->update([
                        'official_code' => $code,
                        'village_no' => (int) substr($code, -2),
                        'name_th' => $name,
                        'source' => 'กรมการปกครอง (DOPA Open Data)',
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
                    continue;
                }

                $latitude = is_numeric($item['oct_side15_lat'] ?? null) ? (float) $item['oct_side15_lat'] : null;
                $longitude = is_numeric($item['oct_side15_lon'] ?? null) ? (float) $item['oct_side15_lon'] : null;
                $upserts[] = [
                    'tambon_id' => $tambonId,
                    'official_code' => $code,
                    'village_no' => (int) substr($code, -2),
                    'name_th' => $name,
                    'latitude' => $latitude !== null && abs($latitude) <= 90 ? $latitude : null,
                    'longitude' => $longitude !== null && abs($longitude) <= 180 ? $longitude : null,
                    'source' => 'กรมการปกครอง (DOPA Open Data)',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            collect($upserts)->chunk(500)->each(fn ($chunk) => DB::table('villages')->upsert(
                $chunk->all(),
                ['official_code'],
                ['tambon_id', 'village_no', 'name_th', 'latitude', 'longitude', 'source', 'is_active', 'updated_at']
            ));
        }
    }
}
