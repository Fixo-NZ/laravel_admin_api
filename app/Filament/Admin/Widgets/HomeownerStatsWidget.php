<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Homeowner;
use Filament\Widgets\Widget;
use App\Filament\Admin\Pages\HomeownerPage;

class HomeownerStatsWidget extends Widget
{
    protected static string $view = 'filament.admin.widgets.homeowner-stats-widget';
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        return [
            'total' => [
                'value' => Homeowner::count(),
                'url' => HomeownerPage::getUrl(),
            ],
            'active' => [
                'value' => Homeowner::where('status', 'active')->count(),
                'url' => HomeownerPage::getUrl(['status' => 'active']),
            ],
            'inactive' => [
                'value' => Homeowner::where('status', 'inactive')->count(),
                'url' => HomeownerPage::getUrl(['status' => 'inactive']),
            ],
            'suspended' => [
                'value' => Homeowner::where('status', 'suspended')->count(),
                'url' => HomeownerPage::getUrl(['status' => 'suspended']),
            ],
        ];
    }
}
