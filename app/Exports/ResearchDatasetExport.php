<?php

namespace App\Exports;

use App\Models\Household;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResearchDatasetExport implements FromArray, WithHeadings
{
    public function __construct(private ?array $householdIds) {}

    public function headings(): array
    {
        return [
            'household_code', 'status', 'farms', 'plots', 'area_rai', 'trees',
            'approved_activities', 'approved_harvest_kg', 'net_benefit_latest',
        ];
    }

    public function array(): array
    {
        return Household::query()
            ->with(['farms.plots.farmActivities', 'farms.plots.harvestRecords', 'sales'])
            ->withCount('farms')
            ->when($this->householdIds !== null, fn ($query) => $query->whereIn('id', $this->householdIds))
            ->orderBy('household_code')
            ->get()
            ->map(function (Household $household) {
                $plots = $household->farms->flatMap->plots;
                $activities = $plots->flatMap->farmActivities;
                $harvests = $plots->flatMap->harvestRecords;
                $latestImpact = \App\Models\EconomicImpact::where('household_id', $household->id)
                    ->latest('calculated_at')->value('net_benefit');

                return [
                    $household->household_code,
                    $household->status,
                    $household->farms_count,
                    $plots->count(),
                    round((float) $plots->sum('area_rai'), 2),
                    (int) $plots->sum('tree_count'),
                    $activities->where('status', 'approved')->count(),
                    round((float) $harvests->where('status', 'approved')->sum('actual_weight_kg'), 2),
                    $latestImpact !== null ? round((float) $latestImpact, 2) : null,
                ];
            })
            ->all();
    }
}
