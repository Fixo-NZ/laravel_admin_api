<?php

namespace App\Filament\Resources\ServiceCategory\Pages;

use App\Filament\Resources\ServiceCategory\ServiceCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceCategory extends CreateRecord
{
    protected static string $resource = ServiceCategoryResource::class;
}
