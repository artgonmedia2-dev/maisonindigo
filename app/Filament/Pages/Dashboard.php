<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HouseStatsWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\LowStockWidget;
use App\Filament\Widgets\MaisonWelcomeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

/**
 * Tableau de bord de la maison : accueil, chiffres du jour, dernières commandes,
 * tailles à réassortir.
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
            LatestOrdersWidget::class,
            LowStockWidget::class,
        ];
    }
}
