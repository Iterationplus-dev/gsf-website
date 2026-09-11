<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use App\Services\MediaService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')
                    ->label('')
                    ->state(fn (Media $record): ?string => $record->isImage()
                        ? app(MediaService::class)->url($record, ['width' => 160, 'height' => 120])
                        : null)
                    ->height(48),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (Media $record): ?string => $record->alt),

                TextColumn::make('mime')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->afterLast('/')->upper()->toString()),

                TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn (int $state): string => round($state / 1024).' KB')
                    ->sortable(),

                TextColumn::make('disk')
                    ->label('Storage')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'cloudinary' ? 'success' : 'gray')
                    ->toggleable(),

                IconColumn::make('approved')
                    ->label('Approved')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('j M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('approved')
                    ->label('Approval')
                    ->options([1 => 'Approved', 0 => 'Awaiting approval']),

                Filter::make('missing_alt')
                    ->label('Missing alt text')
                    ->query(fn ($query) => $query->whereNull('alt')->where('mime', 'like', 'image/%')),
            ])
            ->recordActions([
                EditAction::make(),

                // Deletion goes through the service so the remote file is removed
                // as well as the database row.
                DeleteAction::make()
                    ->using(function (Media $record): void {
                        app(MediaService::class)->delete($record);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->using(function ($records): void {
                            $service = app(MediaService::class);

                            foreach ($records as $record) {
                                $service->delete($record);
                            }
                        }),
                ]),
            ]);
    }
}
