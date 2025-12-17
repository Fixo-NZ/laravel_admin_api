<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

use App\Filament\Admin\Pages\HomeownerPage;
use App\Filament\Admin\Pages\TradiePage;
use App\Filament\Admin\Pages\AdminPage;

class DashboardUserSummaryStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Homeowners
        $homeownerTotal     = Homeowner::query()->toBase()->count();
        $homeownerActive    = Homeowner::query()->where('status', 'active')->toBase()->count();
        $homeownerInactive  = Homeowner::query()->where('status', 'inactive')->toBase()->count();
        $homeownerSuspended = Homeowner::query()->where('status', 'suspended')->toBase()->count();

        // Tradies
        $tradieTotal     = Tradie::query()->toBase()->count();
        $tradieActive    = Tradie::query()->where('status', 'active')->toBase()->count();
        $tradieInactive  = Tradie::query()->where('status', 'inactive')->toBase()->count();
        $tradieSuspended = Tradie::query()->where('status', 'suspended')->toBase()->count();
        $tradiePending   = Tradie::query()->where('status', 'pending')->toBase()->count();

        $tradieAvail   = Tradie::query()->where('availability_status', 'available')->toBase()->count();
        $tradieBusy    = Tradie::query()->where('availability_status', 'busy')->toBase()->count();
        $tradieUnavail = Tradie::query()->where('availability_status', 'unavailable')->toBase()->count();

        // Admins (still counts all users unless filtered to admins)
        $adminTotal     = User::query()->toBase()->count();
        $adminActive    = User::query()->where('status', 'active')->toBase()->count();
        $adminInactive  = User::query()->where('status', 'inactive')->toBase()->count();
        $adminSuspended = User::query()->where('status', 'suspended')->toBase()->count();

        $pill = fn (string $tone, string $label, int $value) =>
            "<span class='inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {$tone}'>
                {$label}: <span class='ml-1 font-semibold'>{$value}</span>
             </span>";

        return [
            Stat::make('Homeowners', number_format($homeownerTotal))
                ->description(new HtmlString(
                    "<div class='mt-1 flex flex-wrap gap-1.5'>
                        {$pill('bg-emerald-50 text-emerald-700 ring-emerald-200', 'Active', $homeownerActive)}
                        {$pill('bg-gray-50 text-gray-700 ring-gray-200', 'Inactive', $homeownerInactive)}
                        {$pill('bg-rose-50 text-rose-700 ring-rose-200', 'Suspended', $homeownerSuspended)}
                     </div>"
                ))
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('info')
                ->url(HomeownerPage::getUrl()),

            Stat::make('Tradies', number_format($tradieTotal))
                ->description(new HtmlString(
                    "<div class='mt-1 space-y-1.5'>
                        <div class='flex flex-wrap gap-1.5'>
                            {$pill('bg-emerald-50 text-emerald-700 ring-emerald-200', 'A', $tradieActive)}
                            {$pill('bg-gray-50 text-gray-700 ring-gray-200', 'I', $tradieInactive)}
                            {$pill('bg-rose-50 text-rose-700 ring-rose-200', 'S', $tradieSuspended)}
                            {$pill('bg-sky-50 text-sky-700 ring-sky-200', 'P', $tradiePending)}
                        </div>
                        <div class='flex flex-wrap gap-1.5'>
                            {$pill('bg-emerald-50 text-emerald-700 ring-emerald-200', 'Avail', $tradieAvail)}
                            {$pill('bg-amber-50 text-amber-800 ring-amber-200', 'Busy', $tradieBusy)}
                            {$pill('bg-rose-50 text-rose-700 ring-rose-200', 'Unavail', $tradieUnavail)}
                        </div>
                    </div>"
                ))
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->url(TradiePage::getUrl()),

            Stat::make('Admins', number_format($adminTotal))
                ->description(new HtmlString(
                    "<div class='mt-1 flex flex-wrap gap-1.5'>
                        {$pill('bg-emerald-50 text-emerald-700 ring-emerald-200', 'Active', $adminActive)}
                        {$pill('bg-gray-50 text-gray-700 ring-gray-200', 'Inactive', $adminInactive)}
                        {$pill('bg-rose-50 text-rose-700 ring-rose-200', 'Suspended', $adminSuspended)}
                     </div>"
                ))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success')
                ->url(AdminPage::getUrl()),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
