<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Pages;

use App\Filament\Admin\Resources\TradieComplaints\TradieComplaintResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\Tradie;
use Carbon\Carbon;

class ViewTradieComplaint extends ViewRecord
{
    protected static string $resource = TradieComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Complaints')
                ->icon('heroicon-o-arrow-left')
                ->url(TradieComplaintResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
