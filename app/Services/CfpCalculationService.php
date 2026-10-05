<?php

namespace App\Services;

use App\Models\BranchDisposalRecord;
use App\Models\CfpCalculation;
use App\Models\CfpParameter;
use App\Models\EmissionFactor;
use App\Models\FarmActivity;
use App\Models\HarvestRecord;
use App\Models\KilnBatch;
use App\Models\Plot;
use App\Models\PlotPreBearingEmission;
use App\Models\PlotProductionCycle;
use App\Models\ProductUsage;
use App\Models\User;
use App\Support\WorkflowStatus;
use Illuminate\Support\Carbon;

/**
 * CFP v0.1 - ตัวคำนวณ Carbon Footprint ของทุเรียน ขอบเขต cradle-to-farm-gate ระดับ "แปลง x ช่วงวันที่ (รอบการผลิต)"
 * หน่วยการทำงาน 1 กก. ทุเรียนสด ตามเอกสาร DRFIS-CFP-Variables / DRFIS-CFP-Cycle-Decision (ในโปรเจกต์)
 *
 * หลักการ
 *  - ใช้เฉพาะข้อมูลสถานะ approved (และไม่ใช่แถว revision) ที่วันที่ของรายการอยู่ในช่วงวันที่
 *  - ไม่ใส่เครดิตสมมติของไบโอชาร์/น้ำส้มควันไม้: ผลลดปุ๋ย/สารเคมี/การเผากิ่งเห็นจากปริมาณจริง
 *    ภาระการผลิตไบโอชาร์/น้ำส้มควันไม้ถูกนับเข้า (ปันส่วนตามมวล) ตามปริมาณที่ใช้ในแปลง
 *  - การกักเก็บคาร์บอนในดินของไบโอชาร์ "ไม่หัก" จากผล (ตัดสินใจแล้ว)
 *  - EF เลือก "ชุดล่าสุด" ตามรหัส (effective_from ล่าสุด) และเก็บ snapshot ไว้ในผล
 *  - ไม่บล็อกเมื่อขาดข้อมูล: ตัดพจน์นั้นออก ลงรายการ warnings และลดคะแนนคุณภาพข้อมูล (ผู้ใช้ตัดสินใจ 24 ก.ย. 2569)
 *  - ค่าเครื่องจักร (แทรกเตอร์/เครื่องสูบน้ำ ฯลฯ) ยังไม่ถูกใช้ เพราะ F13 ยังไม่เก็บชั่วโมงเครื่องจักร; กติกาที่ตกลง:
 *    ถ้ามีทั้งชั่วโมงเครื่องจักรและน้ำมันจริงในรอบเดียวกัน ให้นับน้ำมันจากบันทึกจริงและแจ้งเตือน "อาจนับซ้ำ"
 *
 * ข้อจำกัดของ v0.1 (แสดงใน warnings): ไม่มี N2O ทางอ้อม, ไม่มี N2O จากปุ๋ยอินทรีย์/เศษพืชที่ย่อยสลาย,
 * ไม่มีการผลิตน้ำมัน (ขาดความหนาแน่น), ไม่มี N2O และพลังงานของเตา, ไม่มีขนส่ง/บรรจุภัณฑ์ (นอกขอบเขตถึงประตูสวน)
 *
 * คะแนนคุณภาพข้อมูลเป็นเกณฑ์เบื้องต้นของระบบ (เริ่ม 100 หักตามข้อจำกัด) ยังไม่ใช่เกณฑ์ที่ผู้ทวนสอบรับรอง
 */
class CfpCalculationService
{
    public const VERSION = 'v0.1-farm-gate';

    /** cfp_code ของ materials -> รหัส EF */
    private const ENERGY_EF = [
        'electricity' => 'EF-GRID',
        'diesel_stationary' => 'EF-DIESEL-COMB-STATIONARY',
        'diesel_mobile' => 'EF-DIESEL-COMB-MOBILE',
        'gasoline_stationary' => 'EF-GASOLINE-COMB-STATIONARY',
        'gasoline_mobile' => 'EF-GASOLINE-COMB-MOBILE',
        'water' => 'EF-WATER-PWA',
    ];

    private const FUEL_CODES = ['diesel_stationary', 'diesel_mobile', 'gasoline_stationary', 'gasoline_mobile'];

    private const N_TO_N2O = 44 / 28;

    /** @var array<string, EmissionFactor|null> */
    private array $efCache = [];

    /** @var array<string, CfpParameter|null> */
    private array $paramCache = [];

    /**
     * @return array{result: array, model: ?CfpCalculation}
     */
    public function calculate(
        Plot $plot,
        Carbon $start,
        ?Carbon $end = null,
        ?PlotProductionCycle $cycle = null,
        ?User $by = null,
        bool $persist = true,
    ): array {
        $this->efCache = [];
        $this->paramCache = [];

        $start = $start->copy()->startOfDay();
        $end = ($end ?? now())->copy()->endOfDay();
        $plot->loadMissing('farm');

        $data = $this->gather($plot, $start, $end);
        $central = $this->compute($data, []);

        $harvestKg = $data['harvest_kg'];
        $perKg = fn (float $total) => $harvestKg > 0 ? round($total / $harvestKg, 6) : null;

        $sensitivity = $this->sensitivity($data, $central['total'], $perKg);

        $warnings = $data['warnings'];
        foreach ($central['missing_ef'] as $code) {
            $warnings['missing_ef_'.$code] = "ไม่พบ Emission Factor รหัส {$code} - ตัดพจน์นี้ออกจากผล (ต้องรัน seeder ค่า EF)";
        }

        if ($harvestKg <= 0) {
            $warnings['no_harvest'] = 'ไม่มีผลผลิตที่อนุมัติแล้วในช่วงนี้ จึงคำนวณ CFP ต่อ กก. ไม่ได้ (คำนวณเฉพาะยอดรวม)';
        }

        // EF ที่ยังไม่ผ่านการตรวจต้นฉบับ
        $unverified = collect($central['ef_snapshot'])
            ->filter(fn ($e) => str_contains((string) $e['source_doc_version'], 'รอตรวจ'))
            ->keys()
            ->all();
        if ($unverified) {
            $warnings['ef_unverified'] = 'EF ต่อไปนี้ยังรอตรวจกับเอกสารต้นฉบับ: '.implode(', ', $unverified);
        }

        $score = $this->qualityScore($warnings, $central['missing_ef']);

        $result = [
            'plot_id' => $plot->id,
            'plot_code' => $plot->plot_code,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'boundary' => CfpCalculation::BOUNDARY_FARM_GATE,
            'harvest_kg' => $harvestKg,
            'area_rai' => $plot->area_rai !== null ? (float) $plot->area_rai : null,
            'e_total_kgco2e' => $central['total'],
            'cfp_per_kg' => $perKg($central['total']),
            'cfp_per_rai' => $plot->area_rai > 0 ? round($central['total'] / (float) $plot->area_rai, 4) : null,
            'breakdown' => $central['breakdown'],
            'ef_snapshot' => $central['ef_snapshot'],
            'gwp_version' => 'AR5 (IPCC 2013 GWP100a V1.03)',
            'data_quality_score' => $score,
            'warnings' => array_values($warnings),
            'sensitivity' => $sensitivity,
            'calculation_version' => self::VERSION,
        ];

        $model = $persist ? $this->persist($plot, $start, $end, $cycle, $by, $result) : null;

        return ['result' => $result, 'model' => $model];
    }

    // ---------------------------------------------------------------- gather

    private function gather(Plot $plot, Carbon $start, Carbon $end): array
    {
        $w = [];
        $householdId = $plot->farm->household_id;

        $fert = ['N' => 0.0, 'P2O5' => 0.0, 'K2O' => 0.0, 'urea_kg' => 0.0];
        $organicSkipped = [];
        $aiKg = 0.0;
        $aiUnknown = false;
        $energy = [];
        $prunedKg = 0.0;

        $activities = FarmActivity::with('material')
            ->where('plot_id', $plot->id)
            ->whereNull('original_record_id')
            ->where('status', WorkflowStatus::APPROVED)
            ->whereBetween('activity_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        foreach ($activities as $activity) {
            $m = $activity->material;
            $qty = (float) $activity->quantity;
            if (! $m || $qty <= 0) {
                continue;
            }

            if ($m->category === 'fertilizer') {
                $noNutrients = $m->n_pct === null && $m->p2o5_pct === null && $m->k2o_pct === null;
                if ($noNutrients || str_contains($m->name, 'อินทรีย์')) {
                    $organicSkipped[$m->name] = true;

                    continue;
                }
                $fert['N'] += $qty * (float) $m->n_pct / 100;
                $fert['P2O5'] += $qty * (float) $m->p2o5_pct / 100;
                $fert['K2O'] += $qty * (float) $m->k2o_pct / 100;
                if ($m->is_urea) {
                    $fert['urea_kg'] += $qty;
                }
            } elseif ($m->category === 'chemical') {
                if ($m->active_ingredient_pct === null) {
                    $aiUnknown = true;
                    $aiKg += $qty; // ไม่ทราบ % สารออกฤทธิ์ - ใช้ 100% (อนุรักษ์นิยม; 1 ลิตร ~ 1 กก.)
                } else {
                    $aiKg += $qty * (float) $m->active_ingredient_pct / 100;
                }
            } elseif ($m->cfp_code && isset(self::ENERGY_EF[$m->cfp_code])) {
                $energy[$m->cfp_code] = ($energy[$m->cfp_code] ?? 0.0) + $qty;
            } elseif ($m->category === 'biomass_input') {
                $prunedKg += $qty;
            }
        }

        if ($organicSkipped) {
            $w['organic_fert'] = 'ปุ๋ยอินทรีย์/ปุ๋ยที่ไม่มีสัดส่วน N-P-K ถูกตัดออก (ไม่คำนวณการผลิตและ N2O จากไนโตรเจนอินทรีย์ใน v0.1): '
                .implode(', ', array_keys($organicSkipped));
        }
        if ($aiUnknown) {
            $w['pesticide_ai_unknown'] = 'สารเคมีบางรายการไม่ระบุ % สารออกฤทธิ์ - ใช้ 100% ของน้ำหนักผลิตภัณฑ์ (ค่าอนุรักษ์นิยม) และถือ 1 ลิตร ≈ 1 กก.';
        }
        if (array_intersect(array_keys($energy), self::FUEL_CODES)) {
            $w['fuel_upstream'] = 'นับเฉพาะการเผาไหม้น้ำมัน ยังไม่รวมการผลิตน้ำมัน (ขาดความหนาแน่นน้ำมันที่อ้างอิงได้)';
        }
        if ($fert['N'] > 0) {
            $w['no_indirect_n2o'] = 'ยังไม่คำนวณ N2O ทางอ้อม (ระเหย/ชะละลาย) จากปุ๋ยไนโตรเจน';
        }

        // เก็บเกี่ยว
        $harvestKg = (float) HarvestRecord::where('plot_id', $plot->id)
            ->whereNull('original_record_id')
            ->where('status', WorkflowStatus::APPROVED)
            ->whereBetween('harvest_date', [$start->toDateString(), $end->toDateString()])
            ->sum('actual_weight_kg');

        // กิ่ง/เศษไม้
        $branches = BranchDisposalRecord::where('plot_id', $plot->id)
            ->whereNull('original_record_id')
            ->where('status', WorkflowStatus::APPROVED)
            ->whereBetween('disposal_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $disposedKg = 0.0;
        $burnDmKg = 0.0;
        $moistureMissing = false;
        $hasDecompose = false;
        $paramMoisture = $this->param('BRANCH_MOISTURE_PCT')?->value;

        foreach ($branches as $b) {
            $qty = (float) $b->quantity_kg;
            $disposedKg += $qty;

            if ($b->disposal_route === BranchDisposalRecord::ROUTE_OPEN_BURN) {
                $moisture = $b->moisture_pct !== null
                    ? (float) $b->moisture_pct
                    : ($paramMoisture !== null ? (float) $paramMoisture : null);
                if ($moisture === null) {
                    $moistureMissing = true;
                    $moisture = 0.0; // ไม่ทราบความชื้น - ใช้ 0% (น้ำหนักแห้งสูงสุด = ค่าอนุรักษ์นิยม)
                }
                $burnDmKg += $qty * (1 - $moisture / 100);
            } elseif ($b->disposal_route === BranchDisposalRecord::ROUTE_FIELD_DECOMPOSE) {
                $hasDecompose = true;
            }
        }

        if ($moistureMissing) {
            $w['moisture_missing'] = 'ไม่ทราบความชื้นของกิ่งที่เผา - ใช้ 0% (น้ำหนักแห้งสูงสุด) และยังไม่ใส่ combustion factor';
        }
        if ($hasDecompose) {
            $w['decompose_not_modeled'] = 'กิ่งที่กองทิ้ง/ย่อยสลายในสวน ยังไม่คำนวณ N2O ใน v0.1';
        }
        if ($prunedKg > 0 && abs($prunedKg - $disposedKg) / $prunedKg > 0.10) {
            $w['mass_balance'] = sprintf(
                'กิ่งที่ตัดแต่ง (F13) %.1f กก. ต่างจากผลรวมเส้นทางจัดการกิ่ง %.1f กก. เกิน 10%%',
                $prunedKg,
                $disposedKg
            );
        }

        // เตาผลิต (ระดับครัวเรือน) + การใช้ผลิตภัณฑ์ในแปลง
        $usedQty = (float) ProductUsage::where('plot_id', $plot->id)
            ->whereBetween('application_date', [$start->toDateString(), $end->toDateString()])
            ->with('inventoryTransaction')
            ->get()
            ->sum(fn ($u) => abs((float) ($u->inventoryTransaction->quantity ?? 0)));

        $biocharOutKg = 0.0;
        $totalOut = 0.0;
        if ($usedQty > 0) {
            $batches = KilnBatch::with('outputs.product')
                ->where('household_id', $householdId)
                ->whereNull('original_record_id')
                ->where('status', WorkflowStatus::APPROVED)
                ->where('batch_date', '<=', $end->toDateString())
                ->get();

            foreach ($batches as $batch) {
                foreach ($batch->outputs as $o) {
                    $q = (float) $o->output_quantity;
                    $totalOut += $q;
                    if (str_contains((string) $o->product?->name, 'ไบโอชาร์')) {
                        $biocharOutKg += $q;
                    }
                }
            }

            $w['kiln_n2o_energy'] = 'ภาระของเตานับเฉพาะ CH4 (ยังไม่มีค่า N2O และพลังงานที่ใช้เดินเตา); ปันส่วนตามมวล โดยถือน้ำส้มควันไม้ 1 ลิตร ≈ 1 กก.';
            if ($totalOut <= 0) {
                $w['kiln_no_output'] = 'มีการใช้ผลิตภัณฑ์ชีวมวลในแปลง แต่ไม่พบรอบเดินเตาที่อนุมัติแล้วของครัวเรือน - ไม่คิดภาระเตา';
            }
        }

        // ก่อนให้ผลผลิต
        $pre = PlotPreBearingEmission::where('plot_id', $plot->id)
            ->whereNull('original_record_id')
            ->where('status', WorkflowStatus::APPROVED)
            ->latest('id')
            ->first();

        if (! $pre) {
            $w['no_prebearing'] = 'ไม่มีข้อมูลการปล่อยสะสมช่วงก่อนให้ผลผลิตที่อนุมัติแล้ว - ไม่ปันส่วนพจน์นี้';
        } elseif ($pre->data_basis === PlotPreBearingEmission::BASIS_ESTIMATED) {
            $w['prebearing_estimated'] = 'ข้อมูลช่วงก่อนให้ผลผลิตเป็นค่าประมาณการ';
        }

        return [
            'warnings' => $w,
            'fert' => $fert,
            'ai_kg' => $aiKg,
            'energy' => $energy,
            'harvest_kg' => $harvestKg,
            'burn_dm_kg' => $burnDmKg,
            'kiln_used_qty' => $usedQty,
            'kiln_biochar_out_kg' => $biocharOutKg,
            'kiln_total_out' => $totalOut,
            'prebearing_total' => $pre ? (float) $pre->total_kgco2e : 0.0,
            'economic_life' => $plot->economic_life_years ?: null,
        ];
    }

    // --------------------------------------------------------------- compute

    /**
     * @param  array  $ov  overrides: economic_life, n_ef_code, ef1, kiln_ch4
     */
    private function compute(array $data, array $ov): array
    {
        $b = [];
        $snapshot = [];
        $missing = [];

        $use = function (string $code) use (&$snapshot, &$missing): ?float {
            $ef = $this->ef($code);
            if (! $ef) {
                $missing[$code] = $code;

                return null;
            }
            $snapshot[$code] = [
                'value' => (float) $ef->factor_value,
                'unit' => $ef->unit,
                'source' => $ef->source,
                'source_doc_version' => $ef->source_doc_version,
                'effective_from' => $ef->effective_from?->toDateString(),
            ];

            return (float) $ef->factor_value;
        };

        $gwpCh4 = (float) ($this->param('GWP_CH4')?->value ?? 28);
        $gwpN2o = (float) ($this->param('GWP_N2O')?->value ?? 265);
        $f = $data['fert'];

        // ปุ๋ย - การผลิต
        $nCode = $ov['n_ef_code'] ?? 'EF-FERT-N-UREA';
        if ($f['N'] > 0 && ($v = $use($nCode)) !== null) {
            $b['fert_production_N'] = $f['N'] * $v;
        }
        if ($f['P2O5'] > 0 && ($v = $use('EF-FERT-P2O5')) !== null) {
            $b['fert_production_P2O5'] = $f['P2O5'] * $v;
        }
        if ($f['K2O'] > 0 && ($v = $use('EF-FERT-K2O')) !== null) {
            $b['fert_production_K2O'] = $f['K2O'] * $v;
        }

        // ยูเรีย CO2
        if ($f['urea_kg'] > 0) {
            $b['urea_co2'] = $f['urea_kg'] * (float) ($this->param('UREA_CO2_PER_KG')?->value ?? 0.733);
        }

        // N2O โดยตรงในดิน
        if ($f['N'] > 0) {
            $ef1 = (float) ($ov['ef1'] ?? $this->param('SOIL_N2O_EF1')?->value ?? 0.01);
            $b['soil_n2o_direct'] = $f['N'] * $ef1 * self::N_TO_N2O * $gwpN2o;
        }

        // สารเคมี
        if ($data['ai_kg'] > 0 && ($v = $use('EF-PEST-GENERAL')) !== null) {
            $b['pesticide_production'] = $data['ai_kg'] * $v;
        }

        // พลังงาน/น้ำ
        foreach ($data['energy'] as $cfp => $qty) {
            if (($v = $use(self::ENERGY_EF[$cfp])) !== null) {
                $b['energy_'.$cfp] = $qty * $v;
            }
        }

        // เตาผลิตไบโอชาร์/น้ำส้มควันไม้ (ปันส่วนตามมวล เฉลี่ยทั้งครัวเรือน)
        if ($data['kiln_used_qty'] > 0 && $data['kiln_total_out'] > 0) {
            $ch4 = (float) ($ov['kiln_ch4'] ?? $this->param('KILN_CH4_PER_KG_BIOCHAR')?->value ?? 0);
            $eKiln = $data['kiln_biochar_out_kg'] * $ch4 / 1000 * $gwpCh4;
            $b['kiln_burden'] = $data['kiln_used_qty'] * ($eKiln / $data['kiln_total_out']);
        }

        // เผากิ่งกลางแจ้ง
        if ($data['burn_dm_kg'] > 0) {
            $ch4 = (float) ($this->param('OPEN_BURN_CH4')?->value ?? 0);
            $n2o = (float) ($this->param('OPEN_BURN_N2O')?->value ?? 0);
            $b['branch_open_burn'] = $data['burn_dm_kg'] * ($ch4 * $gwpCh4 + $n2o * $gwpN2o) / 1000;
        }

        // ปันส่วนก่อนให้ผลผลิต
        if ($data['prebearing_total'] > 0) {
            $life = (float) ($ov['economic_life'] ?? $data['economic_life'] ?? $this->param('ECONOMIC_LIFE_YEARS')?->value ?? 25);
            $b['prebearing_allocation'] = $life > 0 ? $data['prebearing_total'] / $life : 0.0;
        }

        $b = array_map(fn ($x) => round($x, 4), $b);

        return [
            'total' => round(array_sum($b), 4),
            'breakdown' => $b,
            'ef_snapshot' => $snapshot,
            'missing_ef' => array_values($missing),
        ];
    }

    private function sensitivity(array $data, float $centralTotal, callable $perKg): array
    {
        $life = $this->param('ECONOMIC_LIFE_YEARS');
        $ef1 = $this->param('SOIL_N2O_EF1');
        $kiln = $this->param('KILN_CH4_PER_KG_BIOCHAR');

        $variants = [];
        if ($life?->sensitivity_low !== null) {
            $variants['อายุสวน '.(float) $life->sensitivity_low.' ปี'] = ['economic_life' => (float) $life->sensitivity_low];
        }
        if ($life?->sensitivity_high !== null) {
            $variants['อายุสวน '.(float) $life->sensitivity_high.' ปี'] = ['economic_life' => (float) $life->sensitivity_high];
        }
        $variants['EF ปุ๋ยไนโตรเจนแบบ EC (สูง)'] = ['n_ef_code' => 'EF-FERT-N-EC'];
        if ($ef1?->sensitivity_low !== null) {
            $variants['EF1 N2O = '.(float) $ef1->sensitivity_low] = ['ef1' => (float) $ef1->sensitivity_low];
        }
        if ($ef1?->sensitivity_high !== null) {
            $variants['EF1 N2O = '.(float) $ef1->sensitivity_high] = ['ef1' => (float) $ef1->sensitivity_high];
        }
        if ($kiln?->sensitivity_low !== null) {
            $variants['CH4 เตา = '.(float) $kiln->sensitivity_low.' g/kg ไบโอชาร์'] = ['kiln_ch4' => (float) $kiln->sensitivity_low];
        }
        if ($kiln?->sensitivity_high !== null) {
            $variants['CH4 เตา = '.(float) $kiln->sensitivity_high.' g/kg ไบโอชาร์'] = ['kiln_ch4' => (float) $kiln->sensitivity_high];
        }

        $out = [];
        foreach ($variants as $label => $ov) {
            $r = $this->compute($data, $ov);
            $out[] = [
                'label' => $label,
                'e_total_kgco2e' => $r['total'],
                'cfp_per_kg' => $perKg($r['total']),
                'change_pct' => $centralTotal > 0 ? round(($r['total'] - $centralTotal) / $centralTotal * 100, 2) : null,
            ];
        }

        return $out;
    }

    private function qualityScore(array $warnings, array $missingEf): float
    {
        $penalty = [
            'organic_fert' => 5, 'pesticide_ai_unknown' => 5, 'fuel_upstream' => 3, 'no_indirect_n2o' => 5,
            'moisture_missing' => 5, 'decompose_not_modeled' => 5, 'mass_balance' => 5, 'kiln_n2o_energy' => 5,
            'kiln_no_output' => 10, 'no_prebearing' => 15, 'prebearing_estimated' => 5, 'ef_unverified' => 10,
            'no_harvest' => 30,
        ];

        $score = 100.0;
        foreach (array_keys($warnings) as $key) {
            $score -= $penalty[$key] ?? 0;
        }
        $score -= 20 * count($missingEf);

        return max(0.0, $score);
    }

    // --------------------------------------------------------------- persist

    private function persist(Plot $plot, Carbon $start, Carbon $end, ?PlotProductionCycle $cycle, ?User $by, array $r): ?CfpCalculation
    {
        $existing = CfpCalculation::where('plot_id', $plot->id)
            ->where('period_start', $start->toDateString())
            ->where('period_end', $end->toDateString())
            ->first();

        // ผลที่ผ่านการทวนสอบ/รับรองแล้วห้ามถูกเขียนทับด้วยการคำนวณใหม่
        if ($existing && $existing->status !== CfpCalculation::STATUS_ESTIMATED) {
            return $existing;
        }

        return CfpCalculation::updateOrCreate(
            ['plot_id' => $plot->id, 'period_start' => $start->toDateString(), 'period_end' => $end->toDateString()],
            [
                'plot_production_cycle_id' => $cycle?->id,
                'boundary' => $r['boundary'],
                'functional_unit' => '1 kg durian',
                'harvest_kg' => $r['harvest_kg'],
                'area_rai' => $r['area_rai'],
                'e_total_kgco2e' => $r['e_total_kgco2e'],
                'cfp_per_kg' => $r['cfp_per_kg'],
                'cfp_per_rai' => $r['cfp_per_rai'],
                'breakdown_json' => $r['breakdown'],
                'ef_snapshot_json' => $r['ef_snapshot'],
                'warnings_json' => $r['warnings'],
                'sensitivity_json' => $r['sensitivity'],
                'gwp_version' => $r['gwp_version'],
                'data_quality_score' => $r['data_quality_score'],
                'status' => CfpCalculation::STATUS_ESTIMATED,
                'calculation_version' => $r['calculation_version'],
                'calculated_by' => $by?->id,
                'calculated_at' => now(),
            ]
        );
    }

    // ------------------------------------------------------------ lookups

    private function ef(string $code): ?EmissionFactor
    {
        if (! array_key_exists($code, $this->efCache)) {
            $this->efCache[$code] = EmissionFactor::where('code', $code)->orderByDesc('effective_from')->first();
        }

        return $this->efCache[$code];
    }

    private function param(string $code): ?CfpParameter
    {
        if (! array_key_exists($code, $this->paramCache)) {
            $this->paramCache[$code] = CfpParameter::where('code', $code)->first();
        }

        return $this->paramCache[$code];
    }
}
