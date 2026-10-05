<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmActivity;
use App\Models\Plot;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SyncController extends Controller
{
    use AuthorizesRequests;

    public function pull(Request $request, AreaScopeService $areaScope)
    {
        $validated = $request->validate([
            'since' => ['nullable', 'date'],
        ]);

        $householdIds = $areaScope->householdIdsFor($request->user());
        $since = isset($validated['since']) ? Carbon::parse($validated['since']) : now()->subDays(30);

        $activities = FarmActivity::query()
            ->whereHas('plot.farm', fn ($query) => $query->when(
                $householdIds !== null,
                fn ($scoped) => $scoped->whereIn('household_id', $householdIds)
            ))
            ->where('updated_at', '>', $since)
            ->orderBy('updated_at')
            ->limit(1000)
            ->get();

        return response()->json([
            'server_time' => now()->toIso8601String(),
            'farm_activities' => $activities,
        ]);
    }

    public function farmActivities(Request $request)
    {
        $payload = $request->validate([
            'records' => ['required', 'array', 'max:100'],
            'records.*.client_uuid' => ['required', 'uuid'],
            'records.*.plot_id' => ['required', 'integer', 'exists:plots,id'],
            'records.*.crop_season_id' => ['required', 'integer', 'exists:crop_seasons,id'],
            'records.*.activity_type_id' => ['required', 'integer', 'exists:activity_types,id'],
            'records.*.activity_date' => ['required', 'date'],
            'records.*.material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'records.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'records.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'records.*.labor_hours' => ['nullable', 'numeric', 'min:0'],
            'records.*.labor_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $results = [];

        foreach ($payload['records'] as $record) {
            $plot = Plot::findOrFail($record['plot_id']);
            $this->authorize('create', [FarmActivity::class, $plot]);

            $activity = FarmActivity::firstOrCreate(
                ['client_uuid' => $record['client_uuid']],
                array_merge($record, [
                    'recorded_by' => $request->user()->id,
                    'total_cost' => round(
                        ((float) ($record['quantity'] ?? 0) * (float) ($record['unit_cost'] ?? 0))
                        + (float) ($record['labor_cost'] ?? 0),
                        2
                    ),
                ])
            );

            $results[] = [
                'client_uuid' => $record['client_uuid'],
                'id' => $activity->id,
                'status' => $activity->wasRecentlyCreated ? 'created' : 'duplicate',
            ];
        }

        return response()->json(['results' => $results], 207);
    }
}
