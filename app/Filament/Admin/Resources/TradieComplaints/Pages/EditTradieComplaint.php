<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Pages;

use App\Filament\Admin\Resources\TradieComplaints\TradieComplaintResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTradieComplaint extends EditRecord
{
    protected static string $resource = TradieComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
