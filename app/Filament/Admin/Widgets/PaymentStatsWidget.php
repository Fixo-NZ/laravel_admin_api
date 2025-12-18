<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsWidget extends BaseWidget
{
    // Filament v3 expects string|null (not int)
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Payments', Payment::count())
                ->description('All payment records')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),

            Stat::make('Total Amount', number_format(Payment::sum('amount'), 2))
                ->description('Sum of all payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Succeeded', Payment::where('status', 'succeeded')->count())
                ->description(
                    'Amount: ' . number_format(
                        Payment::where('status', 'succeeded')->sum('amount'),
                        2
                    )
                )
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending', Payment::where('status', 'pending')->count())
                ->description(
                    'Amount: ' . number_format(
                        Payment::where('status', 'pending')->sum('amount'),
                        2
                    )
                )
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Failed', Payment::where('status', 'failed')->count())
                ->description(
                    'Amount: ' . number_format(
                        Payment::where('status', 'failed')->sum('amount'),
                        2
                    )
                )
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
