<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->write($model, 'create', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === [] || array_diff(array_keys($changes), ['status', 'rejection_reason']) === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        $this->write($model, 'update', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->write($model, 'delete', $model->getOriginal(), null);
    }

    private function write(Model $model, string $action, ?array $old, ?array $new): void
    {
        if (app()->runningInConsole() || $model instanceof AuditLog) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $this->sanitize($old),
            'new_values' => $this->sanitize($new),
            'ip_address' => request()?->ip(),
        ]);
    }

    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach (['password', 'remember_token', 'id_card_number_encrypted'] as $sensitive) {
            unset($values[$sensitive]);
        }

        return $values;
    }
}
