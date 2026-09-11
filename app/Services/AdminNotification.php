<?php

namespace App\Services;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminNotification
{
    public static function record(Model $record, string $operation): Notification
    {
        $label = Str::headline(class_basename($record));
        $attributes = $record->getAttributes();
        $name = $attributes['title'] ?? $attributes['organisation_name'] ?? $attributes['name'] ?? $attributes['label'] ?? $attributes['email'] ?? $attributes['from_path'] ?? $attributes['alt'] ?? $attributes['path'] ?? '#'.$record->getKey();

        return Notification::make()
            ->success()
            ->title($label.' '.$operation)
            ->body(e('“'.Str::limit((string) $name, 120).'” has been '.$operation.'.'))
            ->duration(8000);
    }

    public static function configureActions(): void
    {
        foreach ([DeleteAction::class => 'deleted', ForceDeleteAction::class => 'permanently deleted', RestoreAction::class => 'restored'] as $class => $operation) {
            $class::configureUsing(function (Action $action) use ($operation): void {
                $action->successNotification(fn (Model $record): Notification => self::record($record, $operation));
            });
        }

        foreach ([DeleteBulkAction::class => 'deleted', ForceDeleteBulkAction::class => 'permanently deleted', RestoreBulkAction::class => 'restored'] as $class => $operation) {
            $class::configureUsing(function (Action $action) use ($operation): void {
                $action->successNotification(fn (Action $action): Notification => Notification::make()
                    ->success()
                    ->title('Selected records '.$operation)
                    ->body(e($action->getTotalSelectedRecordsCount().' '.$action->getPluralModelLabel().' have been '.$operation.'.'))
                    ->duration(8000));
            });
        }
    }
}
