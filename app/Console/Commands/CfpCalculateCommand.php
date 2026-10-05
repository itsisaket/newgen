<?php

namespace App\Console\Commands;

use App\Models\Plot;
use App\Models\PlotProductionCycle;
use App\Services\CfpCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * ทดลองคำนวณ CFP v0.1 ของแปลง (ยังไม่มีหน้าจอ) เช่น
 *   php artisan cfp:calculate 1 --start=2025-08-21 --end=2026-08-31
 *   php artisan cfp:calculate PLOT-001 --cycle=3 --save
 * ไม่ใส่ --save = แสดงผลอย่างเดียว ไม่เขียนลงตาราง cfp_calculations
 */
class CfpCalculateCommand extends Command
{
    protected $signature = 'cfp:calculate {plot : รหัสแปลง (id หรือ plot_code)} {--start= : วันเริ่ม YYYY-MM-DD} {--end= : วันสิ้นสุด YYYY-MM-DD} {--cycle= : id รอบการผลิต (ใช้แทน start/end)} {--save : บันทึกผลลง cfp_calculations}';

    protected $description = 'คำนวณ CFP (ถึงประตูสวน) ของแปลงในช่วงวันที่/รอบการผลิต - เวอร์ชันทดลอง v0.1';

    public function handle(CfpCalculationService $service): int
    {
        $arg = $this->argument('plot');
        $plot = Plot::where('plot_code', $arg)->orWhere('id', is_numeric($arg) ? (int) $arg : 0)->first();
        if (! $plot) {
            $this->error("ไม่พบแปลง {$arg}");

            return self::FAILURE;
        }

        $cycle = null;
        if ($this->option('cycle')) {
            $cycle = PlotProductionCycle::where('plot_id', $plot->id)->find($this->option('cycle'));
            if (! $cycle) {
                $this->error('ไม่พบรอบการผลิตนี้ของแปลง');

                return self::FAILURE;
            }
            $start = $cycle->start_date;
            $end = $cycle->end_date;
        } else {
            if (! $this->option('start')) {
                $this->error('ต้องระบุ --start หรือ --cycle');

                return self::FAILURE;
            }
            $start = Carbon::parse($this->option('start'));
            $end = $this->option('end') ? Carbon::parse($this->option('end')) : null;
        }

        $out = $service->calculate($plot, $start, $end, $cycle, null, (bool) $this->option('save'));
        $r = $out['result'];

        $this->info("แปลง {$r['plot_code']}  {$r['period_start']} -> {$r['period_end']}  ({$r['boundary']}, {$r['calculation_version']})");
        $this->line(sprintf('ผลผลิตที่อนุมัติ: %s กก. | พื้นที่: %s ไร่', number_format($r['harvest_kg'], 2), $r['area_rai'] ?? '-'));
        $this->newLine();
        $this->table(['หมวด', 'kgCO2e'], collect($r['breakdown'])->map(fn ($v, $k) => [$k, number_format($v, 4)])->values()->all());
        $this->line('รวม: '.number_format($r['e_total_kgco2e'], 4).' kgCO2e');
        $this->line('CFP ต่อ กก.: '.($r['cfp_per_kg'] !== null ? number_format($r['cfp_per_kg'], 6).' kgCO2e/kg' : '-'));
        $this->line('CFP ต่อไร่: '.($r['cfp_per_rai'] !== null ? number_format($r['cfp_per_rai'], 4).' kgCO2e/ไร่' : '-'));
        $this->line('คะแนนคุณภาพข้อมูล (เบื้องต้น): '.$r['data_quality_score'].' / 100');

        if ($r['sensitivity']) {
            $this->newLine();
            $this->table(['Sensitivity', 'kgCO2e รวม', 'CFP/กก.', 'เปลี่ยน %'], collect($r['sensitivity'])->map(fn ($s) => [
                $s['label'], number_format($s['e_total_kgco2e'], 4), $s['cfp_per_kg'] ?? '-', $s['change_pct'] ?? '-',
            ])->all());
        }

        if ($r['warnings']) {
            $this->newLine();
            $this->warn('คำเตือน/ข้อจำกัด:');
            foreach ($r['warnings'] as $w) {
                $this->line(' - '.$w);
            }
        }

        $this->newLine();
        $this->line($out['model'] ? "บันทึกแล้ว (cfp_calculations id {$out['model']->id}, สถานะ {$out['model']->status})" : 'ไม่ได้บันทึก (ใส่ --save เพื่อบันทึก)');

        return self::SUCCESS;
    }
}
