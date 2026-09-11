<?php

namespace App\Policies;

use App\Models\User;

/**
 * Only a super administrator manages accounts, and nobody may delete their own -
 * an administrator who removes their own access locks the organisation out.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === 'super_admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === 'super_admin';
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role === 'super_admin' && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === 'super_admin';
    }
}
