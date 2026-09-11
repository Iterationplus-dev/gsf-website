<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.view');
    }

    public function view(User $user, Content $content): bool
    {
        return $user->hasPermission('content.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.edit');
    }

    public function update(User $user, Content $content): bool
    {
        return $user->hasPermission('content.edit') && ($content->status === 'draft' || $user->hasPermission('content.publish'));
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->hasPermission('content.publish');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('content.publish');
    }
}
