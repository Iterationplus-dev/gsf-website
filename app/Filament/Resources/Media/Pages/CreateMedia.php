<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Services\AdminNotification;
use App\Services\MediaService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    /**
     * The upload is handed to MediaService rather than written by Filament, so
     * that a Cloudinary installation and a local one take exactly the same path
     * and the record always knows which driver stored its file.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $upload = $data['upload'];

        // A multi-file component would hand back an array; take the single file.
        if (is_array($upload)) {
            $upload = reset($upload);
        }

        abort_unless($upload instanceof UploadedFile, 422, 'No file was received.');

        $media = app(MediaService::class)->store($upload, 'media', [
            'title' => $data['title'] ?? null,
            'alt' => $data['alt'] ?? null,
            'caption' => $data['caption'] ?? null,
            'credit' => $data['credit'] ?? null,
        ]);

        // Approval is a separate, deliberate act, but an administrator who ticked
        // the box on the upload form has already made it.
        if (! empty($data['approved'])) {
            $media->update(['approved' => true]);
        }

        return $media;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return AdminNotification::record($this->getRecord(), 'created');
    }
}
