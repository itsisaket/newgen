<?php

namespace App\Services;

use App\Models\HouseholdBaseline;
use App\Models\KilnBatch;
use App\Models\User;
use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevisionService
{
    public function __construct(private WorkflowService $workflow) {}

    public function createDraft(Model $record, User $user): Model
    {
        return DB::transaction(function () use ($record, $user) {
            $this->workflow->requestRevision($record, $user);

            $revision = $record->replicate();
            $revision->status = WorkflowStatus::DRAFT;
            $revision->rejection_reason = null;
            $revision->recorded_by = $user->id;
            $revision->original_record_id = $record->getKey();

            if ($revision->isFillable('client_uuid')) {
                $revision->client_uuid = (string) Str::uuid();
            }

            if ($revision instanceof KilnBatch) {
                $revision->batch_code = $record->batch_code.'-R'.($record::where('original_record_id', $record->getKey())->count() + 1);
            }

            $revision->save();

            if ($record instanceof HouseholdBaseline) {
                foreach ($record->inputs as $input) {
                    $revision->inputs()->create($input->only($input->getFillable()));
                }
            }

            if ($record instanceof KilnBatch) {
                foreach ($record->outputs as $output) {
                    $revision->outputs()->create($output->only($output->getFillable()));
                }
            }

            return $revision;
        });
    }
}
