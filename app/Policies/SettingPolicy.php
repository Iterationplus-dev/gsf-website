<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy extends OperationsPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('settings.edit');
    }

    public function update(User $user, mixed $record): bool
    {
        return $user->hasPermission('settings.edit');
    }

    /**
     * Settings are edited, never removed: a missing key silently blanks part of
     * the public site rather than failing visibly.
     */
    public function delete(User $user, mixed $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
