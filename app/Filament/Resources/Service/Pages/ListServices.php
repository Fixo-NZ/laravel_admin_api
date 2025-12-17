<?php

namespace App\Filament\Resources\Service\Pages;

<<<<<<< HEAD:app/Filament/Resources/ServiceResource/Pages/ListServices.php
use App\Filament\Resources\ServiceResource;
use App\Filament\Admin\Widgets\ServiceResourceStatsWidget;
=======
use App\Filament\Resources\Service\ServiceResource;
>>>>>>> origin/g2/job_posting:app/Filament/Resources/Service/Pages/ListServices.php
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
