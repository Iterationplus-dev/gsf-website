<?php

namespace App\Filament\Resources\Contents\Tables;

use App\Models\Content;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ContentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (Content $record): string => '/'.$record->slug),

                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => Str::singular(Content::TYPES[$state] ?? $state)),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_demo')
                    ->label('Demo')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->tooltip('Demo content can never be published.'),

                IconColumn::make('featured')->boolean()->toggleable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('j M Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('review_notes')
                    ->label('Needs review')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (): string => 'Review')
                    ->placeholder('')
                    ->tooltip(fn (Content $record): ?string => $record->review_notes),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('type')->options(Content::TYPES)->multiple(),

                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                ]),

                TernaryFilter::make('is_demo')
                    ->label('Demo content')
                    ->placeholder('All records')
                    ->trueLabel('Demo only')
                    ->falseLabel('Real content only'),

                TernaryFilter::make('review_notes')
                    ->label('Awaiting review')
                    ->nullable()
                    ->placeholder('All records')
                    ->trueLabel('Has review notes')
                    ->falseLabel('No review notes'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
