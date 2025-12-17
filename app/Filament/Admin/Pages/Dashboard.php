<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Filament\Admin\Widgets\HomeownerStatsWidget;
use App\Filament\Admin\Widgets\TradieStatsWidget;
use App\Filament\Admin\Widgets\ServiceResourceStatsWidget;
use App\Filament\Admin\Widgets\PaymentStatsWidget;
use App\Filament\Admin\Widgets\DashboardUserSummaryStatsWidget;
use App\Models\Payment;

    class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static string $view = 'filament.admin.pages.dashboard';

    // Display the homeowner stats widget in the page header area
    protected function getHeaderWidgets(): array
    {
        return [
           //HomeownerStatsWidget::class,
            //TradieStatsWidget::class,
            //ServiceResourceStatsWidget::class,
            //PaymentStatsWidget::class,
            DashboardUserSummaryStatsWidget::class,
        ];
    }
}
