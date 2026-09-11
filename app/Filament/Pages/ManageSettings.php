<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Organisational details that change without a deployment: the office address,
 * phone numbers, social profiles and registration information.
 *
 * These are kept out of configuration files precisely so that staff can correct
 * them. Fields left empty stay empty on the public site rather than falling back
 * to a plausible-looking default — a blank registration number is honest, an
 * invented one is not.
 */
class ManageSettings extends Page
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Website settings';

    protected string $view = 'filament.pages.manage-settings';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('settings.edit') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(self::flatten(Setting::values()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Organisation')
                    ->columns(2)
                    ->schema([
                        TextInput::make('org__name')->label('Display name')->required(),
                        TextInput::make('org__legal_name')->label('Full legal name')->required(),
                        TextInput::make('org__short_name')->label('Abbreviation'),
                        TextInput::make('org__founded')->label('Established'),
                        TextInput::make('org__tagline')->label('Tagline')->columnSpanFull(),
                        TextInput::make('org__logo')
                            ->label('Logo URL')
                            ->url()
                            ->helperText('Left empty, the site falls back to the logo bundled with the application.')
                            ->columnSpanFull(),
                        Textarea::make('org__description')->label('Short description')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('Contact')
                    ->description('Shown in the footer, on the contact page, and used to place the map. The map is built from this address, so correct it here rather than anywhere else.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact__address')->label('Street address')->columnSpanFull(),
                        TextInput::make('contact__city')->label('City'),
                        TextInput::make('contact__state')->label('State or region'),
                        TextInput::make('contact__country')->label('Country'),
                        TextInput::make('contact__email')->label('Email address')->email(),
                        TextInput::make('contact__phone_primary')->label('Phone (primary)'),
                        TextInput::make('contact__phone_secondary')->label('Phone (secondary)'),
                        TextInput::make('contact__phone_international')->label('Phone (international)'),
                        TextInput::make('contact__hours')->label('Office hours')->columnSpanFull(),
                    ]),

                Section::make('Social profiles')
                    ->description('Leave a field empty and that platform is simply omitted. Do not enter a personal profile as an organisational account.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('social__linkedin')->label('LinkedIn')->url(),
                        TextInput::make('social__facebook')->label('Facebook')->url(),
                        TextInput::make('social__instagram')->label('Instagram')->url(),
                        TextInput::make('social__x')->label('X')->url(),
                        TextInput::make('social__youtube')->label('YouTube')->url(),
                        TextInput::make('founder__linkedin')->label('Founder LinkedIn')->url(),
                    ]),

                Section::make('Registration')
                    ->description('Published in the footer and in the transparency section once completed. Enter these only from the registration certificate.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('registration__authority')->label('Registering authority'),
                        TextInput::make('registration__number')->label('Registration number'),
                    ]),

                Section::make('Search engines')
                    ->schema([
                        Textarea::make('seo__description')
                            ->label('Default meta description')
                            ->rows(3)
                            ->maxLength(300)
                            ->helperText('Used on pages that do not set their own. Around 155 characters.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);

        foreach ($this->form->getState() as $field => $value) {
            Setting::put(self::expand($field), is_string($value) ? trim($value) : $value);
        }

        Notification::make()->success()->title('Website settings saved')->body('Your organisation details, contact information and social profile settings have been saved.')->duration(8000)->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')->label('Save settings')->submit('save'),
        ];
    }

    /**
     * Setting keys are dotted (`contact.email`), but a Livewire form state path
     * cannot contain a dot without being read as nesting, so they travel through
     * the form as `contact__email`.
     *
     * @param  array<string, string|null>  $settings
     * @return array<string, string|null>
     */
    private static function flatten(array $settings): array
    {
        $flat = [];

        foreach ($settings as $key => $value) {
            $flat[str_replace('.', '__', $key)] = $value;
        }

        return $flat;
    }

    private static function expand(string $field): string
    {
        return str_replace('__', '.', $field);
    }
}
