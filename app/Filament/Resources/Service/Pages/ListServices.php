<?php

namespace App\Filament\Resources\Service\Pages;

use App\Filament\Resources\Service\ServiceResource;
use App\Filament\Admin\Widgets\ServiceResourceStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // =========================================================================
    // HEADER WIDGETS
    // =========================================================================
    protected function getHeaderWidgets(): array
    {
        return [
            ServiceResourceStatsWidget::class,
        ];
    }
}
