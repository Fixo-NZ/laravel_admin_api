<?php

namespace App\Filament\Resources\Tradies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TradieInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('first_name'),
                TextEntry::make('last_name'),
                TextEntry::make('middle_name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('phone'),
                TextEntry::make('email_verified_at')
                    ->dateTime(),
                TextEntry::make('avatar'),
                TextEntry::make('city'),
                TextEntry::make('region'),
                TextEntry::make('postal_code'),
                TextEntry::make('latitude')
                    ->numeric(),
                TextEntry::make('longitude')
                    ->numeric(),
                TextEntry::make('business_name'),
                TextEntry::make('license_number'),
                TextEntry::make('years_experience')
                    ->numeric(),
                TextEntry::make('hourly_rate')
                    ->numeric(),
                TextEntry::make('availability_status'),
                TextEntry::make('service_radius')
                    ->numeric(),
                TextEntry::make('verified_at')
                    ->dateTime(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
