<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * La section « Deux collections » devient éditable : ses textes et sa place
 * dans la page d'accueil se règlent depuis le back-office.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        // Placée juste après le hero : le premier choix du visiteur est
        // « femme ou homme », avant de regarder les modèles.
        $this->add('shop.home_collections_first', true);
        $this->add('shop.home_collections_kicker', 'Deux collections');
        $this->add('shop.home_collections_title', 'Une coupe pour chaque silhouette.');
        $this->add('shop.home_women_title', 'Coupes femme');
        $this->add('shop.home_women_text', 'Wide leg, straight, mom, slim, bootcut, flare.');
        $this->add('shop.home_men_title', 'Coupes homme');
        $this->add('shop.home_men_text', 'Straight, regular, slim, relaxed, tapered.');
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
