<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Homeowner;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Filament\Admin\Pages\HomeownerPage;

class HomeownerStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // HOMEOWNER LIST
            Stat::make('Total Homeowners', Homeowner::count())
                ->description('All registered homeowners')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(HomeownerPage::getUrl()),
            // ACTIVE HOMEOWNERS
            Stat::make('Active Homeowners', Homeowner::where('status', 'active')->count())
                ->description('Currently active accounts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(HomeownerPage::getUrl(['status' => 'active'])),
            // INACTIVE HOMEOWNERS
            Stat::make('Inactive Homeowners', Homeowner::where('status', 'inactive')->count())
                ->description('Inactive accounts')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(HomeownerPage::getUrl(['status' => 'inactive'])),
            // SUSPENDED HOMEOWNERS
            Stat::make('Suspended Homeowners', Homeowner::where('status', 'suspended')->count())
                ->description('Suspended accounts')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning')
                ->url(HomeownerPage::getUrl(['status' => 'suspended'])),

                
        ];
    }
}
    

