<?php

namespace App\Filament\Resources\ServiceCategory\Pages;

use App\Filament\Resources\ServiceCategory\ServiceCategoryResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use App\Filament\Admin\Widgets\ServiceCategoryStatsWidget;

class ManageServiceCategories extends ManageRecords
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->label('New category')
                ->icon('heroicon-m-plus') 
                ->modalHeading('Create Category')
                ->modalSubmitActionLabel('Create'),
        ];
    }

    protected function getTableRecordActions(): array
    {
        return [
            EditAction::make()
                ->modalWidth('10xl')
                ->slideOver()
                ->label('New category')
                ->icon('heroicon-m-plus') 
                ->modalHeading('Create Category')
                ->modalSubmitActionLabel('Create'),
            DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ServiceCategoryStatsWidget::class,
        ];
    }
}
