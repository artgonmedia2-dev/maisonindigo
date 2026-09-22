<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Le visuel et le badge des deux cartes se règlent désormais sur la fiche de
 * la collection, dans Catalogue → Collections. Les réglages correspondants
 * faisaient doublon et créaient deux endroits pour une même décision.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->remove('shop.home_women_image');
        $this->remove('shop.home_women_badge');
        $this->remove('shop.home_men_image');
        $this->remove('shop.home_men_badge');
    }

    /** Rejouable : un réglage déjà retiré ne fait pas échouer la migration. */
    private function remove(string $property): void
    {
        if (! $this->migrator->exists($property)) {
            return;
        }

        $this->migrator->delete($property);
    }
};
