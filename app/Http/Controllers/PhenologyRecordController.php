<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhenologyRecordRequest;
use App\Models\CropSeason;
use App\Models\DurianPhenologyRecord;
use App\Models\Plot;
use App\Services\AreaScopeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * F14-lite - phenology observation log (see the durian_phenology_records
 * migration's doc-comment for why there's no workflow here). Plot-locked
 * create, same convention as FarmActivityController/HarvestRecordController.
 */
class PhenologyRecordController extends Controller
{
    use AuthorizesRequests;

    public function index(AreaScopeService $areaScope)
    {
        $this->authorize('viewAny', DurianPhenologyRecord::class);

        $householdIds = $areaScope->householdIdsFor(auth()->user());

        $records = DurianPhenologyRecord::with(['plot.farm.household', 'recordedBy'])
            ->when($householdIds !== null, fn ($q) => $q->whereHas('plot.farm', fn ($f) => $f->whereIn('household_id', $householdIds)))
            ->latest('observed_date')
            ->paginate(20);

        return view('durian-phenology-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $plot = Plot::with('farm.household')->find($request->integer('plot_id'));

        if (! $plot) {
            return redirect()
                ->route('plots.index')
                ->with('status', 'กรุณาเปิดหน้าแปลงที่ต้องการก่อน แล้วกดปุ่ม "+ บันทึกระยะพัฒนาการ" จากหน้านั้น');
        }

        $this->authorize('create', [DurianPhenologyRecord::class, $plot]);

        return view('durian-phenology-records.create', [
            'plot' => $plot,
            'cropSeasons' => CropSeason::orderByDesc('start_date')->get(['id', 'name']),
        ]);
    }

    public function store(StorePhenologyRecordRequest $request)
    {
        $plot = Plot::with('farm')->findOrFail($request->input('plot_id'));
        $this->authorize('create', [DurianPhenologyRecord::class, $plot]);

        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;

        $record = DurianPhenologyRecord::create($data);

        return redirect()
            ->route('durian-phenology-records.show', $record)
            ->with('status', 'บันทึกระยะพัฒนาการเรียบร้อย');
    }

    public function show(DurianPhenologyRecord $durianPhenologyRecord)
    {
        $this->authorize('view', $durianPhenologyRecord);

        $durianPhenologyRecord->load(['plot.farm.household', 'cropSeason', 'recordedBy']);

        return view('durian-phenology-records.show', ['record' => $durianPhenologyRecord]);
    }
}
