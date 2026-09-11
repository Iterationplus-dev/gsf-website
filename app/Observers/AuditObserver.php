<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Records who changed what, for the records administrators are trusted to edit.
 *
 * Only the names of the changed attributes are stored, never their values: the
 * audit trail must not become a second copy of donor details or draft content
 * sitting outside the access controls that protect the originals.
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model, array_keys($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changed = array_keys($model->getChanges());

        // Touch-only saves are noise.
        if ($changed === ['updated_at'] || $changed === []) {
            return;
        }

        $this->record('updated', $model, $changed);
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, []);
    }

    public function restored(Model $model): void
    {
        $this->record('restored', $model, []);
    }

    /**
     * @param  list<string>  $fields
     */
    private function record(string $action, Model $model, array $fields): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'changed_fields' => array_values(array_diff($fields, ['updated_at', 'created_at'])),
        ]);
    }
}
