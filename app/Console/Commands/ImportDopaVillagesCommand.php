<?php

namespace App\Console\Commands;

use App\Models\Tambon;
use App\Models\Village;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportDopaVillagesCommand extends Command
{
    protected $signature = 'locations:import-villages {files* : ไฟล์ JSON จากชุดข้อมูลหมู่บ้านกรมการปกครอง}';

    protected $description = 'นำเข้าหรืออัปเดตทะเบียนหมู่บ้านจาก DOPA Open Data โดยไม่ลบข้อมูลเดิม';

    public function handle(): int
    {
        $tambonIds = Tambon::pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $provinceIds = DB::table('provinces')->pluck('id', 'name_th')->all();
        $districtIds = DB::table('districts')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $existing = Village::whereNull('official_code')->get()->keyBy(fn (Village $v) => $v->tambon_id.'|'.$this->normalise($v->name_th));
        $totalRows = 0;
        $skipped = 0;
        $now = now();

        $files = collect($this->argument('files'))->flatMap(function ($input) {
            $path = is_dir($input) || is_file($input) ? $input : base_path($input);
            if (is_dir($path)) {
                return glob(rtrim($path, '/\\').'/*.json') ?: [];
            }

            return strpbrk($path, '*?') !== false ? (glob($path) ?: []) : [$path];
        })->values();

        foreach ($files as $file) {
            $path = is_file($file) ? $file : base_path($file);
            if (! is_file($path)) {
                $this->error("ไม่พบไฟล์ {$file}");
                return self::FAILURE;
            }

            $data = json_decode(file_get_contents($path), true);
            if (! is_array($data)) {
                $this->error("ไฟล์ {$file} ไม่ใช่ JSON ที่ถูกต้อง");
                return self::FAILURE;
            }

            $rows = [];

            foreach ($data as $item) {
                $code = str_pad((string) ($item['mcode'] ?? ''), 8, '0', STR_PAD_LEFT);
                $tambonId = (int) substr((string) ($item['tcode'] ?? ''), 0, 6);
                $name = trim((string) ($item['mname'] ?? ''));

                if (strlen($code) !== 8 || $name === '') {
                    $skipped++;
                    continue;
                }

                if (! isset($tambonIds[$tambonId])) {
                    $districtId = (int) ($item['acode'] ?? 0);
                    $provinceId = $provinceIds[trim((string) ($item['pname'] ?? ''))] ?? null;
                    if (! $districtId || ! $provinceId) {
                        $skipped++;
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

                $number = (int) substr($code, -2);
                $manual = $existing->get($tambonId.'|'.$this->normalise($name));
                if ($manual && ! $manual->official_code) {
                    $manual->update([
                        'official_code' => $code,
                        'village_no' => $number,
                        'name_th' => $name,
                        'latitude' => $this->coordinateOrNull($item['oct_side15_lat'] ?? null, 90),
                        'longitude' => $this->coordinateOrNull($item['oct_side15_lon'] ?? null, 180),
                        'source' => 'กรมการปกครอง (DOPA Open Data)',
                        'is_active' => true,
                    ]);
                    continue;
                }

                $rows[] = [
                    'tambon_id' => $tambonId,
                    'official_code' => $code,
                    'village_no' => $number,
                    'name_th' => $name,
                    'latitude' => $this->coordinateOrNull($item['oct_side15_lat'] ?? null, 90),
                    'longitude' => $this->coordinateOrNull($item['oct_side15_lon'] ?? null, 180),
                    'source' => 'กรมการปกครอง (DOPA Open Data)',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::transaction(function () use ($rows) {
                collect($rows)->chunk(500)->each(function ($chunk) {
                DB::table('villages')->upsert(
                    $chunk->all(),
                    ['official_code'],
                    ['tambon_id', 'village_no', 'name_th', 'latitude', 'longitude', 'source', 'is_active', 'updated_at']
                );
                });
            });
            $totalRows += count($rows);
            unset($data, $rows);
        }

        $this->info('นำเข้าทะเบียนหมู่บ้านแล้ว '.number_format($totalRows).' รายการ'.($skipped ? " (ข้าม {$skipped} รายการที่จับคู่ตำบลไม่ได้)" : ''));
        $this->line('ทะเบียนหมู่บ้านในระบบรวม '.number_format(Village::count()).' รายการ');

        return self::SUCCESS;
    }

    private function normalise(string $name): string
    {
        return Str::of($name)
            ->replaceMatches('/^หมู่\s*\d+\s*/u', '')
            ->replaceMatches('/^บ้าน/u', '')
            ->replace(' ', '')
            ->toString();
    }

    private function coordinateOrNull(mixed $value, float $maximum): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return abs($number) <= $maximum ? $number : null;
    }
}
