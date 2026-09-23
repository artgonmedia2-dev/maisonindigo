<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * L'image d'ouverture devient éditable : surtitre, titre, accroche et bouton.
 *
 * Le prix d'appel et le modèle mis en avant, eux, se lisent du catalogue :
 * ce sont des faits, ils n'ont pas à être saisis deux fois.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->add('shop.home_hero_kicker', 'Maison marocaine de denim');
        $this->add('shop.home_hero_title', 'Le bleu, bien coupé.');
        $this->add('shop.home_hero_lead', 'Un denim de 12 à 14 oz, dix-sept tailles en trois longueurs, '
            .'et un guide des tailles sur lequel vous pouvez compter. Le jean que vous gardez.');
        $this->add('shop.home_hero_cta_label', 'Découvrir les jeans homme');
        $this->add('shop.home_hero_cta_url', '/homme');
    }

    /** Rejouable : un réglage déjà saisi garde sa valeur. */
    private function add(string $property, mixed $value): void
    {
        if ($this->migrator->exists($property)) {
            return;
        }

        $this->migrator->add($property, $value);
    }
};
