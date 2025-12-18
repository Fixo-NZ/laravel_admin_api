<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\HomeownerJobOffer; // change if your job post model is different
use App\Filament\Resources\JobPost\JobPostResource;

class JobPostStatsWidget extends StatsOverviewWidget
{
    // IMPORTANT (Filament v3): heading is NON-static
    protected ?string $heading = 'Job Posts Overview';

    protected function getStats(): array
    {
        $q = HomeownerJobOffer::query();

        return [
    Stat::make('Total Job Posts', (clone $q)->count())
        ->description('All job posts')
        ->descriptionIcon('heroicon-m-clipboard-document-list')
        ->color('primary')
        ->url(JobPostResource::getUrl()),

    Stat::make('Open', (clone $q)->where('status', 'open')->count())
        ->description('Currently available')
        ->descriptionIcon('heroicon-m-megaphone')
        ->color('success')
        ->url(JobPostResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => 'open']],
        ])),

    Stat::make('Assigned', (clone $q)->where('status', 'assigned')->count())
        ->description('Tradie assigned')
        ->descriptionIcon('heroicon-m-user-plus')
        ->color('info')
        ->url(JobPostResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => 'assigned']],
        ])),

    Stat::make('In Progress', (clone $q)->where('status', 'in_progress')->count())
        ->description('Work ongoing')
        ->descriptionIcon('heroicon-m-wrench-screwdriver')
        ->color('warning')
        ->url(JobPostResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => 'in_progress']],
        ])),

    Stat::make('Completed', (clone $q)->where('status', 'completed')->count())
        ->description('Finished jobs')
        ->descriptionIcon('heroicon-m-check-badge')
        ->color('success')
        ->url(JobPostResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => 'completed']],
        ])),

    Stat::make('Cancelled', (clone $q)->where('status', 'cancelled')->count())
        ->description('Stopped jobs')
        ->descriptionIcon('heroicon-m-x-circle')
        ->color('danger')
        ->url(JobPostResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => 'cancelled']],
        ])),
];

   
    }
}
