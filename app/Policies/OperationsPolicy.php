<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared authorisation for operational records that are read and worked, but
 * never publicly published: enquiries, subscribers, campaigns, metrics,
 * redirects and audit history.
 */
abstract class OperationsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('operations.view');
    }

    public function view(User $user, mixed $record): bool
    {
        return $user->hasPermission('operations.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('operations.edit');
    }

    public function update(User $user, mixed $record): bool
    {
        return $user->hasPermission('operations.edit');
    }

    public function delete(User $user, mixed $record): bool
    {
        return $user->hasPermission('operations.edit');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('operations.edit');
    }
}
