<?php

namespace App\Filament\Resources\JobPost\Pages;

use App\Filament\Resources\JobPost\JobPostResource;
use Filament\Resources\Pages\CreateRecord;

use App\Filament\Admin\Widgets\JobPostStatsWidget;

class CreateJobPost extends CreateRecord
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            JobPostStatsWidget::class,
        ];
    }
}
