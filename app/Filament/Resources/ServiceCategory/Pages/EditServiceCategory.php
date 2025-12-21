<?php

namespace App\Filament\Resources\ServiceCategory\Pages;

use App\Filament\Resources\ServiceCategory\ServiceCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceCategory extends EditRecord
{
    protected static string $resource = ServiceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
