<?php

namespace App\Filament\Resources\JobPost\Pages;

use App\Filament\Resources\JobPost\JobPostResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

use App\Filament\Admin\Widgets\JobPostStatsWidget;

class EditJobPost extends EditRecord
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobPostStatsWidget::class,
        ];
    }
}
