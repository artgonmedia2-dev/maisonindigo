<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Le bandeau de logos sous l'ouverture.
 *
 * L'intitulé est obligatoire et éditable : des marques tierces affichées sans
 * contexte laissent croire que la boutique les revend. C'est à la maison de
 * poser la formulation vraie.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->add('shop.home_brands_enabled', true);
        $this->add('shop.home_brands_title', 'Les maisons qui ont façonné le denim');
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
