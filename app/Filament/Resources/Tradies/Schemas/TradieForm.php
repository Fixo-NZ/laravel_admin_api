<?php

namespace App\Filament\Resources\Tradies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TradieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('middle_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('avatar')
                    ->default(null),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('address')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->default(null),
                TextInput::make('region')
                    ->default(null),
                TextInput::make('postal_code')
                    ->default(null),
                TextInput::make('latitude')
                    ->numeric()
                    ->default(null),
                TextInput::make('longitude')
                    ->numeric()
                    ->default(null),
                TextInput::make('business_name')
                    ->default(null),
                TextInput::make('license_number')
                    ->default(null),
                TextInput::make('trade_type')
                    ->default(null),
                Textarea::make('insurance_details')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('years_experience')
                    ->numeric()
                    ->default(null),
                TextInput::make('hourly_rate')
                    ->numeric()
                    ->default(null),
                Select::make('availability_status')
                    ->options(['available' => 'Available', 'busy' => 'Busy', 'unavailable' => 'Unavailable'])
                    ->default('available')
                    ->required(),
                TextInput::make('service_radius')
                    ->required()
                    ->numeric()
                    ->default(50),
                DateTimePicker::make('verified_at'),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
            ]);
    }
}
