<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('media.edit') || $user->hasPermission('content.view');
    }

    public function view(User $user, Media $media): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('media.edit');
    }

    public function update(User $user, Media $media): bool
    {
        return $user->hasPermission('media.edit');
    }

    /**
     * Deletion removes the file from the storage provider as well as the record,
     * so it is restricted to the roles that can publish rather than to everyone
     * who can upload.
     */
    public function delete(User $user, Media $media): bool
    {
        return $user->hasPermission('content.publish');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('content.publish');
    }
}
