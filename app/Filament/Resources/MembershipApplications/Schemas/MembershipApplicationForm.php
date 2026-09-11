<?php

namespace App\Filament\Resources\MembershipApplications\Schemas;

use App\Models\MembershipApplication;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * What the society submitted is a record of what they said, so every field they
 * filled in is read-only. Staff change only the review outcome and their notes.
 */
class MembershipApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Review')
                ->schema([
                    Select::make('status')
                        ->options(MembershipApplication::STATUSES)
                        ->required()
                        ->native(false),
                    Textarea::make('review_notes')
                        ->label('Internal notes')
                        ->helperText('Not shown to the applicant.')
                        ->rows(4),
                ]),

            Section::make('Application')
                ->description('Submitted by the applicant and kept as received.')
                ->columns(2)
                ->schema([
                    TextInput::make('organisation_name')->label('Organisation')->disabled(),
                    Select::make('cooperative_type')->options(MembershipApplication::TYPES)->disabled(),
                    TextInput::make('member_count')->label('Members declared')->disabled(),
                    TextInput::make('organisation_website')->label('Website')->disabled(),
                    TextInput::make('contact_name')->disabled(),
                    TextInput::make('contact_phone')->disabled(),
                    TextInput::make('email')->label('Email address')->disabled(),
                    TextInput::make('telephone')->disabled(),
                    TextInput::make('ethnic_group')->disabled(),
                    TextInput::make('consented_at')->label('Declaration confirmed')->disabled(),
                    Textarea::make('organisation_address')->rows(3)->disabled()->columnSpanFull(),
                    Textarea::make('message')->label('Additional information')->rows(5)->disabled()->columnSpanFull(),
                ]),
        ]);
    }
}
