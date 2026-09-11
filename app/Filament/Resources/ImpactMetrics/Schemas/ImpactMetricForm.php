<?php

namespace App\Filament\Resources\ImpactMetrics\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ImpactMetricForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('The figure')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->helperText('What is being counted — for example "Entrepreneurs trained".'),

                    TextInput::make('value')
                        ->numeric()
                        ->required()
                        ->helperText('The figure itself. Enter the number you can evidence, not a rounded-up estimate.'),

                    TextInput::make('unit')
                        ->maxLength(50)
                        ->helperText('Optional — for example "people", "co-operatives", "hours".'),

                    TextInput::make('year')
                        ->numeric()
                        ->minValue(2015)
                        ->maxValue((int) now()->addYear()->format('Y'))
                        ->helperText('The reporting year this figure covers.'),

                    TextInput::make('geography')
                        ->maxLength(255)
                        ->helperText('Where the figure applies — for example "Rivers State, Nigeria".'),

                    TextInput::make('position')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower numbers appear first.'),
                ]),

            Section::make('Evidence')
                ->description('A published figure is a claim made to donors. The source is what makes it defensible.')
                ->schema([
                    Textarea::make('source')
                        ->rows(3)
                        ->required()
                        ->helperText('Where this figure comes from: attendance registers, training reports, registration records, a specific project report. A metric saved without a source is forced back to unpublished.'),

                    Toggle::make('published')
                        ->label('Published on the website')
                        ->helperText('Only published metrics with a recorded source and value appear publicly.'),
                ]),
        ]);
    }
}
