<?php

namespace App\Filament\Resources\Contents\Schemas;

use App\Models\Content;
use App\Models\Media;
use App\Models\Project;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([
                Tab::make('Content')->schema(self::editorial()),
                Tab::make('Project details')
                    ->schema(self::projectDetails())
                    ->visible(fn ($get): bool => $get('type') === 'project'),
                Tab::make('Publishing')->schema(self::publishing()),
                Tab::make('SEO')->schema(self::seo()),
                Tab::make('Provenance')->schema(self::provenance()),
            ]),
        ]);
    }

    /**
     * @return list<mixed>
     */
    private static function editorial(): array
    {
        return [
            Section::make()->columns(2)->schema([
                Select::make('type')
                    ->options(Content::TYPES)
                    ->required()
                    ->live()
                    ->disabledOn('edit')
                    ->helperText('The type decides where the record appears and cannot be changed afterwards.'),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, $set, $get, string $operation): void {
                        // Only auto-generate on create: changing a published slug
                        // breaks every existing inbound link to the page.
                        if ($operation === 'create' && filled($state) && blank($get('slug'))) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('The web address. Changing it on a published page breaks existing links — add a redirect if you do.'),

                TextInput::make('category')
                    ->maxLength(255)
                    ->helperText('For people this is their role; for articles, the section.'),

                Textarea::make('excerpt')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->helperText('One or two sentences. Used on cards, in search results and as the default page description.'),

                RichEditor::make('body')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold', 'italic', 'link', 'bulletList', 'orderedList',
                        'h2', 'h3', 'blockquote', 'undo', 'redo',
                    ]),
            ]),

            Section::make('Image')->schema([
                Select::make('image_id')
                    ->label('Featured image')
                    ->relationship('image', 'title')
                    ->searchable()
                    ->preload()
                    ->helperText('Only approved media is displayed publicly. Upload files under Media first.'),
            ]),

            Section::make('Tags')->schema([
                TagsInput::make('tags')->helperText('Optional keywords.'),
            ]),
        ];
    }

    /**
     * Project-specific delivery data, saved to the related project record.
     *
     * @return list<mixed>
     */
    private static function projectDetails(): array
    {
        return [
            Fieldset::make('Delivery')
                ->relationship('project')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(Project::STATUSES)
                        ->required()
                        ->default('planned'),

                    Select::make('program_id')
                        ->label('Program')
                        ->options(fn (): array => Content::where('type', 'program')->orderBy('title')->pluck('title', 'id')->all())
                        ->searchable(),

                    TextInput::make('location')->maxLength(255),
                    TextInput::make('state')->maxLength(255),
                    TextInput::make('country')->maxLength(255)->default('Nigeria'),
                    TextInput::make('beneficiary_category')->label('Beneficiary group')->maxLength(255),

                    DateTimePicker::make('start_date')->date(),
                    DateTimePicker::make('end_date')->date(),

                    TextInput::make('beneficiary_count')
                        ->label('People reached')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Leave empty rather than estimating. Published figures must be supportable.'),

                    Select::make('sdgs')
                        ->label('Sustainable Development Goals')
                        ->multiple()
                        ->options([
                            1 => '1 — No Poverty',
                            2 => '2 — Zero Hunger',
                            4 => '4 — Quality Education',
                            5 => '5 — Gender Equality',
                            8 => '8 — Decent Work and Economic Growth',
                            9 => '9 — Industry, Innovation and Infrastructure',
                            10 => '10 — Reduced Inequalities',
                            11 => '11 — Sustainable Cities and Communities',
                            12 => '12 — Responsible Consumption and Production',
                            17 => '17 — Partnerships for the Goals',
                        ])
                        ->helperText('Select a goal only where the project genuinely contributes to it.'),

                    Textarea::make('objectives')->rows(4)->columnSpanFull()->helperText('One per line.'),
                    Textarea::make('activities')->rows(4)->columnSpanFull()->helperText('One per line.'),
                    Textarea::make('outcomes')->rows(4)->columnSpanFull()->helperText('One per line.'),
                    Textarea::make('key_results')->rows(4)->columnSpanFull()->helperText('One per line.'),

                    Select::make('gallery')
                        ->multiple()
                        ->options(fn (): array => Media::approved()->where('mime', 'like', 'image/%')->orderByDesc('id')->pluck('title', 'id')->all())
                        ->searchable(),

                    Select::make('documents')
                        ->multiple()
                        ->options(fn (): array => Media::approved()->where('mime', 'not like', 'image/%')->orderByDesc('id')->pluck('title', 'id')->all())
                        ->searchable(),

                    TagsInput::make('partners')->helperText('Delivery partners on this project.'),
                    TagsInput::make('donors')->label('Funders')->helperText('Only funders who have agreed to be named.'),

                    Select::make('manager_id')
                        ->label('Project manager')
                        ->options(fn (): array => User::orderBy('name')->pluck('name', 'id')->all())
                        ->searchable(),

                    TextInput::make('public_budget_minor')
                        ->label('Published budget (minor units)')
                        ->numeric()
                        ->helperText('Only complete this where the budget has been approved for publication.'),

                    TextInput::make('budget_currency')->label('Budget currency')->maxLength(3),
                ]),
        ];
    }

    /**
     * @return list<mixed>
     */
    private static function publishing(): array
    {
        $canPublish = fn (): bool => auth()->user()?->hasPermission('content.publish') ?? false;

        return [
            Section::make()->columns(2)->schema([
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->required()
                    ->disabled(fn (): bool => ! $canPublish())
                    ->helperText('Only a content manager or administrator can publish. Scheduled records appear automatically once their publication time passes.'),

                DateTimePicker::make('published_at')
                    ->label('Publication date and time')
                    ->disabled(fn (): bool => ! $canPublish())
                    ->helperText('Required for the record to appear publicly.'),

                Toggle::make('featured')
                    ->helperText('Featured records are promoted on the homepage and listings.'),

                TextInput::make('position')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first in ordered listings.'),

                Toggle::make('is_demo')
                    ->label('Demo content')
                    ->columnSpanFull()
                    ->helperText('Demo records are forced to draft and can never appear publicly. Untick only once the content is verified organisational fact.'),
            ]),
        ];
    }

    /**
     * @return list<mixed>
     */
    private static function seo(): array
    {
        return [
            Section::make()->schema([
                TextInput::make('seo_title')
                    ->maxLength(255)
                    ->helperText('Defaults to the title if left empty. Around 60 characters reads best in search results.'),

                Textarea::make('seo_description')
                    ->rows(3)
                    ->maxLength(300)
                    ->helperText('Defaults to the excerpt if left empty. Around 155 characters.'),
            ]),
        ];
    }

    /**
     * @return list<mixed>
     */
    private static function provenance(): array
    {
        return [
            Section::make()
                ->description('Where this content came from and what still needs checking. Never published; these fields exist so that a future editor can tell fact from placeholder.')
                ->schema([
                    Textarea::make('source')
                        ->rows(2)
                        ->helperText('The document, page or person this content came from.'),

                    Textarea::make('review_notes')
                        ->rows(4)
                        ->helperText('What remains unverified, and who needs to confirm it.'),
                ]),
        ];
    }
}
