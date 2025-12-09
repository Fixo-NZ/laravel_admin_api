<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tradie;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Filament\Admin\Pages\TradiePage;

class TradieStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // TRADIE LIST
            Stat::make('Total Tradies', Tradie::count())
                ->description('All registered tradies')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(TradiePage::getUrl()),

            // ACTIVE TRADIES
            Stat::make('Active Tradies', Tradie::where('status', 'active')->count())
                ->description('Currently active accounts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(TradiePage::getUrl(['status' => 'active'])),
            
            // INACTIVE TRADIES
            Stat::make('Inactive Tradies', Tradie::where('status', 'inactive')->count())
                ->description('Inactive accounts')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(TradiePage::getUrl(['status' => 'inactive'])),
            
            // SUSPENDED TRADIES
            Stat::make('Suspended Tradies', Tradie::where('status', 'suspended')->count())
                ->description('Suspended accounts')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning')
                ->url(TradiePage::getUrl(['status' => 'suspended'])),

            // AVAILABLE TRADIES
            Stat::make('Available Tradies', Tradie::where('availability_status', 'available')->count())
                ->description('Available for jobs')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(TradiePage::getUrl(['availability_status' => 'available'])),

            // BUSY TRADIES
            Stat::make('Busy Tradies', Tradie::where('availability_status', 'busy')->count())
                ->description('Currently working')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->url(TradiePage::getUrl(['availability_status' => 'busy'])),

            // UNAVAILABLE TRADIES
            Stat::make('Unavailable Tradies', Tradie::where('availability_status', 'unavailable')->count())
                ->description('Not accepting jobs')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('danger')
                ->url(TradiePage::getUrl(['availability_status' => 'unavailable'])),
                      
            // PENDING TRADIES
            Stat::make('Pending Tradies', Tradie::where('status', 'pending')->count())
                ->description('Pending accounts')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('info')
                ->url(TradiePage::getUrl(['statuss' => 'pending'])),
            
        
        ];
    }
}
    

