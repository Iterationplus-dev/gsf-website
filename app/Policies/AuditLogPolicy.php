<?php

namespace App\Policies;

use App\Models\User;

/**
 * The audit trail is evidence. It is readable by administrators and writable by
 * nobody through the interface.
 */
class AuditLogPolicy extends OperationsPolicy
{
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, mixed $record): bool
    {
        return false;
    }

    public function delete(User $user, mixed $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
