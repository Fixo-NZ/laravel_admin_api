<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class PaymentStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Payment Status Distribution';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        Payment::where('status', 'succeeded')->count(),
                        Payment::where('status', 'pending')->count(),
                        Payment::where('status', 'failed')->count(),
                    ],
                ],
            ],
            'labels' => ['Succeeded', 'Pending', 'Failed'],
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
