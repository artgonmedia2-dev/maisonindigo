<?php

namespace App\Filament\Resources\Cuts\Schemas;

use App\Enums\Gender;
use App\Models\Cut;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CutForm
{
    /**
     * Le nom se change quand on veut ; l'identifiant se fige dès qu'un produit
     * l'emploie, car il voyage dans les adresses de la boutique et les SKU.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label(__('admin.cuts.fields.name'))
                        ->helperText(__('admin.cuts.fields.name_hint'))
                        ->required()
                        ->maxLength(60)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, ?Cut $record): void {
                            if ($record === null) {
                                $set('slug', Str::slug((string) $state, '_'));
                                $set('sku_code', Str::upper(Str::substr(Str::ascii((string) $state), 0, 3)));
                            }
                        }),

                    TextInput::make('slug')
                        ->label(__('admin.cuts.fields.slug'))
                        ->helperText(fn (?Cut $record): string => $record?->isUsed() === true
                            ? __('admin.cuts.locked')
                            : __('admin.cuts.fields.slug_hint'))
                        ->required()
                        ->maxLength(20)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?Cut $record): bool => $record?->isUsed() === true)
                        ->dehydrated(),

                    TextInput::make('sku_code')
                        ->label(__('admin.cuts.fields.sku_code'))
                        ->helperText(__('admin.cuts.fields.sku_code_hint'))
                        ->required()
                        ->minLength(3)
                        ->maxLength(3)
                        ->alpha()
                        ->formatStateUsing(fn (?string $state): ?string => $state === null ? null : Str::upper($state))
                        ->dehydrateStateUsing(fn (string $state): string => Str::upper($state)),

                    TextInput::make('position')
                        ->label(__('admin.cuts.fields.position'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(9999)
                        ->default(100)
                        ->required(),

                    CheckboxList::make('genders')
                        ->label(__('admin.cuts.fields.genders'))
                        ->options(self::genderOptions())
                        ->required()
                        ->columns(2)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label(__('admin.cuts.fields.is_active'))
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function genderOptions(): array
    {
        $options = [];

        foreach (Gender::cases() as $gender) {
            $options[$gender->value] = $gender->getLabel();
        }

        return $options;
    }
}
