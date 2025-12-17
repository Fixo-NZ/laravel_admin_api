<?php

namespace App\Filament\Resources\JobPost\Pages;

use App\Filament\Resources\JobPost\JobPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobPost extends EditRecord
{
    protected static string $resource = JobPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
