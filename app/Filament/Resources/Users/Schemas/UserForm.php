<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Account management for super administrators.
 *
 * Two-factor secrets and recovery codes are deliberately absent: they belong to
 * the account holder, they are stored encrypted, and no administration screen
 * has any reason to display or edit them.
 */
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(254),

                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->rule(Password::min(12)->letters()->numbers()->symbols()->uncompromised())
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->helperText('At least 12 characters with letters, numbers and symbols. Leave empty when editing to keep the current password.')
                        ->columnSpanFull(),
                ]),

            Section::make('Access')
                ->description('Roles carry a baseline set of permissions. Additional grants are added on top of the role and never subtract from it.')
                ->columns(2)
                ->schema([
                    Select::make('role')
                        ->options(User::ROLES)
                        ->required()
                        ->default('editor')
                        ->native(false),

                    Toggle::make('is_active')
                        ->label('Account active')
                        ->helperText('An inactive account is refused everywhere, whatever its role. Deactivate rather than delete when someone leaves, so their audit history stays attributable.'),

                    Select::make('permissions')
                        ->label('Additional permissions')
                        ->multiple()
                        ->options([
                            'content.view' => 'View content',
                            'content.edit' => 'Edit content',
                            'content.publish' => 'Publish content',
                            'media.edit' => 'Manage media',
                            'operations.view' => 'View operational records',
                            'operations.edit' => 'Edit operational records',
                            'settings.edit' => 'Edit website settings',
                            'donations.view' => 'View donations',
                            'donations.export' => 'Export donations',
                            'donations.receipt' => 'Resend donation receipts',
                        ])
                        ->columnSpanFull()
                        ->helperText('Grant sparingly. Donation permissions expose donor names, email addresses and amounts.'),
                ]),
        ]);
    }
}
