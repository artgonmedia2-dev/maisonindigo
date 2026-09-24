<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * La section des avis clients sur la page d'accueil.
 *
 * Désactivée au départ : une boutique qui vient d'ouvrir n'a pas d'avis, et
 * une section vide vaut moins qu'une section absente.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->add('shop.home_reviews_enabled', false);
        $this->add('shop.home_reviews_title', 'Ce qu’en disent nos clients');
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
