<?php

namespace App\Services;

use App\Models\Household;

/**
 * Research Dashboard - "Data Completeness" (Blueprint หัวข้อ 14, Research
 * Dashboard metric list). Blueprint names this metric but gives no
 * literal formula, so this defines it plainly and auditably: of the 4
 * core field-data forms every household is expected to record at least
 * once (F01 Baseline, F13 Farm Activity, F08 Harvest, F14 Phenology),
 * what fraction of in-scope households have at least one row in each -
 * not a weighted composite score, so a program manager can see exactly
 * which form is lagging rather than one opaque number.
 */
class DataCompletenessService
{
    public const EXPECTED_FORMS = [
        'baseline' => 'F01 Baseline',
        'activity' => 'F13 กิจกรรมสวน',
        'harvest' => 'F08 ผลผลิต',
        'phenology' => 'F14 ระยะพัฒนาการ',
    ];

    /**
     * @param  array<int>|null  $householdIds  null = ไม่จำกัดขอบเขต (ดูได้ทุกครัวเรือน)
     * @return array{total_households: int, counts: array<string,int>, pct: array<string,float>, avg_completeness_pct: float}
     */
    public function summarize(?array $householdIds): array
    {
        $base = Household::query()->when($householdIds !== null, fn ($q) => $q->whereIn('id', $householdIds));

        $total = (clone $base)->count();

        if ($total === 0) {
            $zeros = array_fill_keys(array_keys(self::EXPECTED_FORMS), 0);

            return [
                'total_households' => 0,
                'counts' => $zeros,
                'pct' => array_map(fn () => 0.0, $zeros),
                'avg_completeness_pct' => 0.0,
            ];
        }

        $counts = [
            'baseline' => (clone $base)->whereHas('baselines', fn ($q) => $q->whereNull('original_record_id'))->count(),
            'activity' => (clone $base)->whereHas('farms.plots.farmActivities', fn ($q) => $q->whereNull('original_record_id'))->count(),
            'harvest' => (clone $base)->whereHas('farms.plots.harvestRecords', fn ($q) => $q->whereNull('original_record_id'))->count(),
            'phenology' => (clone $base)->whereHas('farms.plots.phenologyRecords')->count(),
        ];

        $pct = [];
        foreach ($counts as $key => $count) {
            $pct[$key] = round($count / $total * 100, 1);
        }

        return [
            'total_households' => $total,
            'counts' => $counts,
            'pct' => $pct,
            'avg_completeness_pct' => round(array_sum($pct) / count($pct), 1),
        ];
    }
}
