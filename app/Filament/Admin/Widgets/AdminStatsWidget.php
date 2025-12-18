<?php

    namespace App\Filament\Admin\Widgets;

    use App\Models\User;
    use Filament\Widgets\StatsOverviewWidget as BaseWidget;
    use Filament\Widgets\StatsOverviewWidget\Stat;

    use App\Filament\Admin\Pages\AdminPage; 

    class AdminStatsWidget extends BaseWidget
    {
        protected function getStats(): array
        {
            return [
                // ADMIN LIST
                Stat::make('Total Admins', User::count())
                    ->description('All admins')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary')
                    ->url(AdminPage::getUrl()),
                // ACTIVE ADMINS
                Stat::make('Active Admins', User::where('status', 'active')->count())
                    ->description('Currently active accounts')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success')
                    ->url(AdminPage::getUrl(['status' => 'active'])),
                // INACTIVE ADMINS
                Stat::make('Inactive Admins', User::where('status', 'inactive')->count())
                    ->description('Inactive accounts')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger')
                    ->url(AdminPage::getUrl(['status' => 'inactive'])),
                // SUSPENDED ADMINS
                Stat::make('Suspended Admins', User::where('status', 'suspended')->count())
                    ->description('Suspended accounts')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('warning')
                    ->url(AdminPage::getUrl(['status' => 'suspended'])),
            ];
        }
    }
