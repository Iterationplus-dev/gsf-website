<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Models\Enquiry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * What someone submitted is a record of what they said, so every field they
 * filled in is read-only here. The only thing staff change is how far the
 * enquiry has got.
 */
class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Handling')
                ->schema([
                    Select::make('status')
                        ->options(Enquiry::STATUSES)
                        ->required()
                        ->native(false),
                ]),

            Section::make('Submission')
                ->description('Submitted by the enquirer and kept as received.')
                ->columns(2)
                ->schema([
                    Select::make('type')->options(Enquiry::TYPES)->disabled(),
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->label('Email address')->disabled(),
                    TextInput::make('phone')->disabled(),
                    TextInput::make('organization')->disabled(),
                    TextInput::make('consented_at')->label('Consent recorded')->disabled(),
                    Textarea::make('message')->rows(8)->disabled()->columnSpanFull(),
                ]),
        ]);
    }
}
