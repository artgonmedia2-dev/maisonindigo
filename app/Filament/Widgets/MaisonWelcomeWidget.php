<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * Bandeau d'accueil du tableau de bord : salutation, signature, prochaines étapes.
 */
class MaisonWelcomeWidget extends Widget
{
    protected static ?int $sort = -10;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.maison-welcome';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Filament::auth()->user();

        return [
            'name' => $user?->getAttribute('name') ?? '',
            'tagline' => config('maison.tagline'),
        ];
    }
}
