<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Réglages éditables depuis le back-office. Ils priment sur config/maison.php,
 * qui ne garde que les valeurs techniques et les secrets.
 */
class ShopSettings extends Settings
{
    public string $contact_email;

    public string $contact_whatsapp;

    public string $contact_city;

    /** Montant en centimes à partir duquel la livraison est annoncée offerte. */
    public int $free_shipping_threshold;

    public int $exchange_days;

    public ?string $bank_holder;

    public ?string $bank_name;

    public ?string $bank_iban;

    public bool $announcement_enabled;

    public ?string $announcement_text;

    public static function group(): string
    {
        return 'shop';
    }
}
