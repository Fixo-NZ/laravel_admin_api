<?php

namespace App\Filament\Resources\Tradies\Pages;

use App\Filament\Resources\Tradies\TradieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTradies extends ListRecords
{
    protected static string $resource = TradieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
