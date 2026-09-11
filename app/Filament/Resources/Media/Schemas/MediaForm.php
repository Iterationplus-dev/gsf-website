<?php

namespace App\Filament\Resources\Media\Schemas;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaForm
{
    /**
     * Accepted upload types. Anything outside this list is rejected before the
     * file reaches the storage driver, and the browser-declared type is checked
     * again server-side by the validation rules Filament generates.
     *
     * @var list<string>
     */
    public const ACCEPTED = [
        'image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif',
        'application/pdf',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('File')
                ->description('Uploads go to the configured media provider. The file cannot be changed after upload — delete the record and upload again instead.')
                ->schema([
                    // storeFiles(false) hands the create page the live upload so
                    // that MediaService, not Filament, decides where it is stored.
                    FileUpload::make('upload')
                        ->label('File')
                        ->required()
                        ->storeFiles(false)
                        ->acceptedFileTypes(self::ACCEPTED)
                        ->maxSize(20 * 1024)
                        ->helperText('Images and PDFs, up to 20 MB. Upload the highest-quality original you have: display sizes are generated on delivery.')
                        ->visibleOn('create')
                        ->columnSpanFull(),
                ]),

            Section::make('Description')
                ->description('Alt text is what a screen reader announces and what search engines index. Describe what the image shows, not that it is an image.')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->helperText('An internal label, used to find this file again.'),

                    TextInput::make('alt')
                        ->label('Alt text')
                        ->maxLength(255)
                        ->helperText('Leave empty only for purely decorative images.'),

                    Textarea::make('caption')
                        ->rows(2)
                        ->maxLength(500)
                        ->helperText('Shown beneath the image where it is displayed.'),

                    TextInput::make('credit')
                        ->maxLength(255)
                        ->helperText('Photographer or source, where attribution is required.'),
                ])
                ->columns(2),

            Section::make('Publication')
                ->schema([
                    Toggle::make('approved')
                        ->label('Approved for publication')
                        ->helperText('Unapproved media is never rendered on the public site. Confirm you have consent to publish images of identifiable people before approving.'),

                    Select::make('collection')
                        ->label('Show in')
                        ->options([Media::GALLERY => 'Photo gallery'])
                        ->placeholder('Not in any gallery')
                        ->native(false)
                        ->helperText('Approval allows a file to be used; this decides whether it also appears in the public photo gallery. Leave empty for images that belong only to the page they illustrate.'),
                ]),
        ]);
    }
}
