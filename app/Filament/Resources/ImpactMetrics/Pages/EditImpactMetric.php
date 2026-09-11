<?php

namespace App\Filament\Resources\ImpactMetrics\Pages;

use App\Filament\Resources\ImpactMetrics\ImpactMetricResource;
use App\Services\AdminNotification;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditImpactMetric extends EditRecord
{
    protected static string $resource = ImpactMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return AdminNotification::record($this->getRecord(), 'saved');
    }
}
