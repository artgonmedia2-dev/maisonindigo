<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->add('shop.contact_email', (string) config('maison.contact.email'));
        $this->add('shop.contact_whatsapp', (string) config('maison.contact.whatsapp'));
        $this->add('shop.contact_city', (string) config('maison.contact.city'));
        $this->add('shop.free_shipping_threshold', 60000);
        $this->add('shop.exchange_days', 14);
        $this->add('shop.bank_holder', config('maison.bank.holder'));
        $this->add('shop.bank_name', config('maison.bank.name'));
        $this->add('shop.bank_iban', config('maison.bank.iban'));
        $this->add('shop.announcement_enabled', false);
        $this->add('shop.announcement_text', 'Livraison offerte dès 600 dh, partout au Maroc.');
    }

    /**
     * Rejouable : un réglage déjà présent garde la valeur saisie dans le
     * back-office. Utile quand la migration est relancée après un redéploiement.
     */
    private function add(string $property, mixed $value): void
    {
        if ($this->migrator->exists($property)) {
            return;
        }

        $this->migrator->add($property, $value);
    }
};
