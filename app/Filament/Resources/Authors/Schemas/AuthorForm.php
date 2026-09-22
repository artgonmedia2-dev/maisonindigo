<?php

namespace App\Filament\Resources\Authors\Schemas;

use App\Models\Author;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('admin.authors.fields.name'))
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, ?Author $record): void {
                            if ($record === null) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label(__('admin.authors.fields.slug'))
                        ->required()
                        ->maxLength(190)
                        ->alphaDash()
                        ->unique(ignoreRecord: true),

                    TextInput::make('role')
                        ->label(__('admin.authors.fields.role'))
                        ->maxLength(120),

                    TextInput::make('email')
                        ->label(__('admin.authors.fields.email'))
                        ->email()
                        ->maxLength(190),

                    Textarea::make('bio')
                        ->label(__('admin.authors.fields.bio'))
                        ->helperText(__('admin.authors.fields.bio_hint'))
                        ->rows(4)
                        ->maxLength(800)
                        ->columnSpanFull(),

                    SpatieMediaLibraryFileUpload::make('portrait')
                        ->label(__('admin.authors.fields.portrait'))
                        ->collection(Author::MEDIA_PORTRAIT)
                        ->disk(config('media-library.disk_name'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios(['1:1'])
                        ->maxSize(4096)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
