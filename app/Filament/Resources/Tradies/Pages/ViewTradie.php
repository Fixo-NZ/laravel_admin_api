<?php

namespace App\Filament\Resources\Tradies\Pages;

use App\Filament\Resources\Tradies\TradieResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTradie extends ViewRecord
{
    protected static string $resource = TradieResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
