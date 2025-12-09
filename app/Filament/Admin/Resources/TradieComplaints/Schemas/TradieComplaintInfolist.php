<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TradieComplaintInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tradie_id')
                    ->numeric(),
                TextEntry::make('homeowner_id')
                    ->numeric(),
                TextEntry::make('title'),
                TextEntry::make('status'),
                TextEntry::make('reviewed_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
