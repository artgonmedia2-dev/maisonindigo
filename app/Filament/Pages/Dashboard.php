<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HouseStatsWidget;
use App\Filament\Widgets\MaisonWelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

/**
 * Tableau de bord de la maison : accueil, chiffres du jour.
 * Les résumés de commandes et de stock arrivent avec les sprints 1 et 4.
 */
class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard.title');
    }

    public function getTitle(): string
    {
        return __('admin.dashboard.title');
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            MaisonWelcomeWidget::class,
            HouseStatsWidget::class,
        ];
    }
}
