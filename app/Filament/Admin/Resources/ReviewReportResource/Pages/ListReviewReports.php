<?php

namespace App\Filament\Admin\Resources\ReviewReportResource\Pages;

use App\Filament\Admin\Resources\ReviewReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviewReports extends ListRecords
{
    protected static string $resource = ReviewReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
