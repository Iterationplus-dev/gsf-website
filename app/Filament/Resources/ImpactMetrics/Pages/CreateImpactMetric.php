<?php

namespace App\Filament\Resources\ImpactMetrics\Pages;

use App\Filament\Resources\ImpactMetrics\ImpactMetricResource;
use App\Services\AdminNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateImpactMetric extends CreateRecord
{
    protected static string $resource = ImpactMetricResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return AdminNotification::record($this->getRecord(), 'created');
    }
}
