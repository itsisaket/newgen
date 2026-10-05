<?php

namespace App\Http\Controllers;

use App\Models\BranchDisposalRecord;
use App\Models\CarbonActivity;
use App\Models\FarmActivity;
use App\Models\HarvestRecord;
use App\Models\HouseholdBaseline;
use App\Models\KilnBatch;
use App\Services\RevisionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class RevisionController extends Controller
{
    use AuthorizesRequests;

    private const TYPES = [
        'household-baseline' => HouseholdBaseline::class,
        'farm-activity' => FarmActivity::class,
        'harvest-record' => HarvestRecord::class,
        'kiln-batch' => KilnBatch::class,
        'carbon-activity' => CarbonActivity::class,
        'branch-disposal-record' => BranchDisposalRecord::class,
    ];

    public function store(Request $request, string $type, int $id, RevisionService $revisions)
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        /** @var Model $record */
        $record = self::TYPES[$type]::findOrFail($id);
        $this->authorize('view', $record);

        $revision = $revisions->createDraft($record, $request->user());

        return redirect()->route($this->showRoute($type), $revision)
            ->with('status', 'สร้างฉบับแก้ไขใหม่แล้ว ระเบียนเดิมยังคงเก็บไว้เป็นประวัติ');
    }

    private function showRoute(string $type): string
    {
        return match ($type) {
            'household-baseline' => 'household-baselines.show',
            'farm-activity' => 'farm-activities.show',
            'harvest-record' => 'harvest-records.show',
            'kiln-batch' => 'kiln-batches.show',
            'carbon-activity' => 'carbon-activities.show',
            'branch-disposal-record' => 'branch-disposal-records.show',
        };
    }
}
