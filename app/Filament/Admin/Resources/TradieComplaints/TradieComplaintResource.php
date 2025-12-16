<?php

namespace App\Filament\Admin\Resources\TradieComplaints;

use App\Filament\Admin\Resources\TradieComplaints\Pages\CreateTradieComplaint;
use App\Filament\Admin\Resources\TradieComplaints\Pages\EditTradieComplaint;
use App\Filament\Admin\Resources\TradieComplaints\Pages\ListTradieComplaints;
use App\Filament\Admin\Resources\TradieComplaints\Pages\ViewTradieComplaint;
use App\Filament\Admin\Resources\TradieComplaints\Schemas\TradieComplaintForm;
use App\Filament\Admin\Resources\TradieComplaints\Schemas\TradieComplaintInfolist;
use App\Filament\Admin\Resources\TradieComplaints\Tables\TradieComplaintsTable;
use App\Models\TradieComplaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TradieComplaintResource extends Resource
{
    protected static ?string $model = TradieComplaint::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static \UnitEnum|string|null $navigationGroup = 'Complaints';
    protected static ?string $navigationLabel = 'Tradies';
    protected static ?string $title = 'F Tradies';

    public static function form(Schema $schema): Schema
    {
        return TradieComplaintForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TradieComplaintInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TradieComplaintsTable::configure($table);
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
            'index' => ListTradieComplaints::route('/'),
            'view' => ViewTradieComplaint::route('/{record}'),
        ];
    }
}
