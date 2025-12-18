<?php

namespace App\Filament\Resources\Service;

use App\Filament\Resources\Service\Pages;
use App\Filament\Resources\Service\Pages\ManageServices;
use App\Filament\Resources\Service\Schemas\ServicesForm;
use App\Filament\Resources\Service\Tables\ServicesTable;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;
use App\Filament\Admin\Widgets\ServiceResourceStatsWidget;


class ServiceResource extends Resource
{
    // ============================================================
    // PAGE CONFIGURATION
    // ============================================================

    // The Eloquent model this resource manages
    protected static ?string $model = Service::class;

    // Icon to display in the sidebar (use Heroicons names)
    protected static ?string $navigationIcon = null;
    
    // Label for the navigation item 
    protected static ?string $navigationLabel = 'Services';

    // Navigation group in the sidebar
    protected static ?string $navigationGroup = 'Job Oversight';
    
    // Model Label
    protected static ?string $modelLabel = 'Services';

    // Slug for the resource URLs
    protected static ?string $slug = 'jobs/services';

    // Auto-refresh interval (in seconds)
    protected static int $pollingInterval = 5;

    // ============================================================
    // FORM DEFINITION
    // ============================================================
    public static function form(Form $form): Form
    {
        return ServicesForm::configure($form);
    }

    // ============================================================
    // TABLE DEFINITION
    // ============================================================   
    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServices::route('/'),

            // Original generated pages
            // 'index' => Pages\ListServices::route('/'),
            // 'create' => Pages\CreateService::route('/create'),
            // 'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
