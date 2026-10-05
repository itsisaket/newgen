<?php

namespace App\Services;

use App\Exceptions\WorkflowTransitionException;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Support\WorkflowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Blueprint section 16.1 + Appendix D (Workflow State Transition Matrix).
 *
 * The ONE place every F01-F15 module goes through to change a record's
 * status. Never set `$model->status = ...` directly in a controller -
 * always call one of these methods, so every transition is checked against
 * the matrix and written to audit_logs the same way everywhere.
 *
 * Any model used here is expected to have: status, rejection_reason
 * (nullable), recorded_by (FK to users - the "owner" for the --- -> Draft
 * and Draft -> Submitted steps). Verified/approved actor + timestamp
 * columns are optional; set them via $extra if the migration has them.
 */
class WorkflowService
{
    public function submit(Model $model, User $user): Model
    {
        $this->assertCurrentStatus($model, WorkflowStatus::DRAFT);

        if ((int) $model->recorded_by !== (int) $user->id && ! $user->hasRole(Role::SUPER_ADMIN)) {
            throw new WorkflowTransitionException('เฉพาะเจ้าของข้อมูลเท่านั้นที่ส่งข้อมูลนี้ได้');
        }

        return $this->transition($model, $user, WorkflowStatus::SUBMITTED, 'submit');
    }

    public function verify(Model $model, User $user): Model
    {
        $this->assertCurrentStatus($model, WorkflowStatus::SUBMITTED);
        $this->assertHasAnyRole($user, [Role::RESEARCHER, Role::DISTRICT_OFFICER, Role::SUPER_ADMIN]);

        return $this->transition($model, $user, WorkflowStatus::VERIFIED, 'verify');
    }

    public function approve(Model $model, User $user): Model
    {
        $this->assertCurrentStatus($model, WorkflowStatus::VERIFIED);
        $this->assertHasAnyRole($user, [Role::PROJECT_ADMIN, Role::SUPER_ADMIN]);

        return $this->transition($model, $user, WorkflowStatus::APPROVED, 'approve');
    }

    /**
     * Reject bounces the record back one step: Submitted -> Draft (by
     * Researcher/District Officer) or Verified -> Submitted (by Project
     * Admin). A reason is mandatory both times (Blueprint Appendix D).
     */
    public function reject(Model $model, User $user, string $reason): Model
    {
        if (trim($reason) === '') {
            throw new WorkflowTransitionException('ต้องระบุเหตุผลในการตีกลับทุกครั้ง');
        }

        $current = $model->status;

        if ($current === WorkflowStatus::SUBMITTED) {
            $this->assertHasAnyRole($user, [Role::RESEARCHER, Role::DISTRICT_OFFICER, Role::SUPER_ADMIN]);
            $target = WorkflowStatus::DRAFT;
        } elseif ($current === WorkflowStatus::VERIFIED) {
            $this->assertHasAnyRole($user, [Role::PROJECT_ADMIN, Role::SUPER_ADMIN]);
            $target = WorkflowStatus::SUBMITTED;
        } else {
            throw new WorkflowTransitionException("ไม่สามารถตีกลับข้อมูลที่สถานะ [{$current}] ได้");
        }

        return $this->transition($model, $user, $target, 'reject', ['rejection_reason' => $reason]);
    }

    /**
     * Approved -> Revision Requested. The controller is responsible for
     * then creating a NEW record with original_record_id pointing back
     * here (Blueprint 16.1) - this method only flips the flag on the
     * original so it stays visible as history.
     */
    public function requestRevision(Model $model, User $user): Model
    {
        $this->assertCurrentStatus($model, WorkflowStatus::APPROVED);
        $this->assertHasAnyRole($user, [Role::PROJECT_ADMIN, Role::RESEARCHER, Role::SUPER_ADMIN]);

        return $this->transition($model, $user, WorkflowStatus::REVISION_REQUESTED, 'request_revision');
    }

    protected function transition(Model $model, User $user, string $to, string $action, array $extra = []): Model
    {
        $from = $model->status;

        return DB::transaction(function () use ($model, $user, $from, $to, $action, $extra) {
            $model->forceFill(array_merge(['status' => $to], $extra))->save();

            AuditLog::create([
                'user_id' => $user->id,
                'auditable_type' => $model::class,
                'auditable_id' => $model->getKey(),
                'action' => $action,
                'old_values' => ['status' => $from],
                'new_values' => array_merge(['status' => $to], $extra),
                'ip_address' => request()?->ip(),
            ]);

            return $model->refresh();
        });
    }

    protected function assertCurrentStatus(Model $model, string $expected): void
    {
        if ($model->status !== $expected) {
            throw new WorkflowTransitionException(
                "ต้องอยู่ในสถานะ [{$expected}] เท่านั้น (ปัจจุบัน: {$model->status})"
            );
        }
    }

    protected function assertHasAnyRole(User $user, array $roles): void
    {
        if (! $user->hasAnyRole($roles)) {
            throw new WorkflowTransitionException('คุณไม่มีสิทธิ์ดำเนินการขั้นตอนนี้');
        }
    }
}
