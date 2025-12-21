<?php

namespace App\Filament\Resources\JobPost\Pages;

use App\Filament\Resources\JobPost\JobPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use App\Filament\Admin\Widgets\JobPostStatsWidget;

class ListJobPosts extends ListRecords
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JobPostStatsWidget::class,
        ];
    }
}
    