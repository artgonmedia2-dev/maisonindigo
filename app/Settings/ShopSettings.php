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

    public string $home_hero_kicker;

    public string $home_hero_title;

    public string $home_hero_lead;

    public string $home_hero_cta_label;

    /** Chemin interne, par exemple /homme/jean-baggy. */
    public string $home_hero_cta_url;

    /** La section « Deux collections » passe avant les nouveautés. */
    public bool $home_collections_first;

    public string $home_collections_kicker;

    public string $home_collections_title;

    public string $home_women_title;

    public string $home_women_text;

    public string $home_men_title;

    public string $home_men_text;

    public bool $announcement_enabled;

    public ?string $announcement_text;

    public static function group(): string
    {
        return 'shop';
    }
}
