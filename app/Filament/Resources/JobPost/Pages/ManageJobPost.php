<?php

namespace App\Filament\Resources\JobPost\Pages;

use App\Filament\Resources\JobPost\JobPostResource;
use Filament\Resources\Pages\ManageRecords;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

use App\Filament\Admin\Widgets\JobPostStatsWidget;

class ManageJobPost extends ManageRecords
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl')
                ->label('New Job Post')
                ->icon('heroicon-m-plus')
                ->modalHeading('Create Job Post')
                ->modalSubmitActionLabel('Create'),
        ];
    }

    protected function getTableRecordActions(): array
    {
        return [
            EditAction::make()
                ->modalWidth('4xl')
                ->label('Edit')
                ->icon('heroicon-m-pencil-square')
                ->modalHeading('Edit Job Post')
                ->modalSubmitActionLabel('Save'),
            DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobPostStatsWidget::class,
        ];
    }
}
