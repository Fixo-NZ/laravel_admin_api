<?php

namespace App\Filament\Resources\Tradies\Pages;

use App\Filament\Resources\Tradies\TradieResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTradie extends EditRecord
{
    protected static string $resource = TradieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
