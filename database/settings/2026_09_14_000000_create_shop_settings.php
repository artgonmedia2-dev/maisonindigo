<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shop.contact_email', (string) config('maison.contact.email'));
        $this->migrator->add('shop.contact_whatsapp', (string) config('maison.contact.whatsapp'));
        $this->migrator->add('shop.contact_city', (string) config('maison.contact.city'));
        $this->migrator->add('shop.free_shipping_threshold', 60000);
        $this->migrator->add('shop.exchange_days', 14);
        $this->migrator->add('shop.bank_holder', config('maison.bank.holder'));
        $this->migrator->add('shop.bank_name', config('maison.bank.name'));
        $this->migrator->add('shop.bank_iban', config('maison.bank.iban'));
        $this->migrator->add('shop.announcement_enabled', false);
        $this->migrator->add('shop.announcement_text', 'Livraison offerte dès 600 dh, partout au Maroc.');
    }
};
