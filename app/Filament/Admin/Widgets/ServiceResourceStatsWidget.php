<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Filament\Resources\Service\ServiceResource;
class ServiceResourceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // SERVICE LIST
            Stat::make('Total Services', Service::count())
                ->description('All registered services')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(ServiceResource::getUrl()),

                /*
                 * NOTE:
                 * This page belongs to a **Resource**, not a custom Page.
                 * Resource::getUrl() ALWAYS generates the URL for the resource’s
                 * default page (usually the "index" list page).
                 *
                 * Example:
                 * /admin/jobs/services   ← because the Resource slug is "jobs/services"
                 */

            // ACTIVE SERVICES
            Stat::make('Active Services', Service::where('status', 'active')->count())
                ->description('Currently active services')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(ServiceResource::getUrl() . '?status=active'),
                
                /*
                 * NOTE:
                 * For Resources, getUrl() does NOT accept query params as an array
                 * in the first argument. We must manually append "?status=active"
                 * to trigger filtering logic inside the Resource table.
                 */

            // INACTIVE SERVICES
            Stat::make('Inactive Services', Service::where('status', 'inactive')->count())
                ->description('Inactive services')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(ServiceResource::getUrl() . '?status=inactive'),
            // SUSPENDED SERVICES
            Stat::make('Suspended Services', Service::where('status', 'suspended')->count())
                ->description('Suspended services')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning')
                ->url(ServiceResource::getUrl() . '?status=suspended'),
        ];
    }
}
