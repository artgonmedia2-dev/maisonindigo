<?php

namespace App\Filament\Pages;

use App\Filament\Support\MoneyField;
use App\Settings\ShopSettings;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Réglages de la boutique : coordonnées, livraison, virement, bandeau.
 */
class ManageShopSettings extends SettingsPage
{
    protected static string $settings = ShopSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.title');
    }

    public function getTitle(): string
    {
        return __('admin.settings.title');
    }

    public function getSavedNotificationTitle(): ?string
    {
        return __('admin.settings.saved');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.settings.sections.contact'))
                    ->description(__('admin.settings.sections.contact_hint'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('contact_email')
                            ->label(__('admin.settings.fields.contact_email'))
                            ->email()
                            ->required()
                            ->maxLength(190),

                        TextInput::make('contact_whatsapp')
                            ->label(__('admin.settings.fields.contact_whatsapp'))
                            ->tel()
                            ->required()
                            ->placeholder('+212600000000')
                            ->maxLength(20),

                        TextInput::make('contact_city')
                            ->label(__('admin.settings.fields.contact_city'))
                            ->required()
                            ->maxLength(80),
                    ]),

                Section::make(__('admin.settings.sections.shipping'))
                    ->columns(2)
                    ->schema([
                        MoneyField::make('free_shipping_threshold', __('admin.settings.fields.free_shipping_threshold'))
                            ->helperText(__('admin.settings.fields.free_shipping_hint'))
                            ->required(),

                        TextInput::make('exchange_days')
                            ->label(__('admin.settings.fields.exchange_days'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(90)
                            ->required(),
                    ]),

                Section::make(__('admin.settings.sections.bank'))
                    ->description(__('admin.settings.sections.bank_hint'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('bank_holder')
                            ->label(__('admin.settings.fields.bank_holder'))
                            ->maxLength(190),

                        TextInput::make('bank_name')
                            ->label(__('admin.settings.fields.bank_name'))
                            ->maxLength(190),

                        TextInput::make('bank_iban')
                            ->label(__('admin.settings.fields.bank_iban'))
                            ->maxLength(60),
                    ]),

                Section::make(__('admin.settings.sections.announcement'))
                    ->columns(1)
                    ->schema([
                        Toggle::make('announcement_enabled')
                            ->label(__('admin.settings.fields.announcement_enabled')),

                        Textarea::make('announcement_text')
                            ->label(__('admin.settings.fields.announcement_text'))
                            ->helperText(__('admin.settings.fields.announcement_hint'))
                            ->rows(2)
                            ->maxLength(180),
                    ]),
            ]);
    }
}
