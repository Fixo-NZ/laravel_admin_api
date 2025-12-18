<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PaymentRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue (Last 30 Days)';
    protected static ?string $pollingInterval = '15s';

    protected function getData(): array
    {
        $payments = Payment::where('status', 'succeeded')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->get()
            ->groupBy(fn ($p) => Carbon::parse($p->created_at)->format('Y-m-d'));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $payments->map(fn ($day) => $day->sum('amount'))->values(),
                ],
            ],
            'labels' => $payments->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
