<?php

namespace App\Filament\Resources\Service\Pages;

use App\Filament\Resources\Service\ServiceResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use App\Filament\Admin\Widgets\ServiceResourceStatsWidget;

class ManageServices extends ManageRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->label('New service')
                ->icon('heroicon-m-plus') 
                ->modalHeading('Create Service')
                ->modalSubmitActionLabel('Create'),
        ];
    }

    protected function getTableRecordActions(): array
    {
        return [
            EditAction::make()
                ->modalWidth('4xl')
                ->label('New service')
                ->icon('heroicon-m-plus') 
                ->modalHeading('Create Service')
                ->modalSubmitActionLabel('Create'),
            DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ServiceResourceStatsWidget::class,
        ];
    }
}
