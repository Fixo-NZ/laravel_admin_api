<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Pages;

use App\Filament\Admin\Resources\TradieComplaints\TradieComplaintResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTradieComplaint extends ViewRecord
{
    protected static string $resource = TradieComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
