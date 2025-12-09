<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TradieComplaintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tradie_id')
                    ->required()
                    ->numeric(),
                TextInput::make('homeowner_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'dismissed' => 'Dismissed'])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('reviewed_at'),
            ]);
    }
}
