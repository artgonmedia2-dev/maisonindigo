<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Les deux cartes de la page d'accueil reçoivent leur visuel et leur badge.
 * Sans image, le gabarit denim d'origine reste affiché.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->add('shop.home_women_image', null);
        $this->add('shop.home_women_badge', 'new');
        $this->add('shop.home_men_image', null);
        $this->add('shop.home_men_badge', 'atelier');
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
