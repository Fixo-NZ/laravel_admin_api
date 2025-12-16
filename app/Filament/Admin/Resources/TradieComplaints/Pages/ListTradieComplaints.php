<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Pages;

use App\Filament\Admin\Resources\TradieComplaints\TradieComplaintResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTradieComplaints extends ListRecords
{
    protected static string $resource = TradieComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
