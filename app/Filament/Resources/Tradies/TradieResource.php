<?php

namespace App\Filament\Resources\Tradies;

use App\Filament\Resources\Tradies\Pages\CreateTradie;
use App\Filament\Resources\Tradies\Pages\EditTradie;
use App\Filament\Resources\Tradies\Pages\ListTradies;
use App\Filament\Resources\Tradies\Pages\ViewTradie;
use App\Filament\Resources\Tradies\Schemas\TradieForm;
use App\Filament\Resources\Tradies\Schemas\TradieInfolist;
use App\Filament\Resources\Tradies\Tables\TradiesTable;
use App\Models\Tradie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TradieResource extends Resource
{
    protected static ?string $model = Tradie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Schema $schema): Schema
    {
        return TradieForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TradieInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TradiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTradies::route('/'),
            'create' => CreateTradie::route('/create'),
            'view' => ViewTradie::route('/{record}'),
            'edit' => EditTradie::route('/{record}/edit'),
        ];
    }
}
