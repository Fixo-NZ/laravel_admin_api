<?php

namespace App\Filament\Resources\ServiceCategory;

use App\Filament\Resources\ServiceCategory\Pages;
use App\Models\ServiceCategory;
use App\Filament\Resources\ServiceCategory\Tables\ServiceCategoriesTable;
use App\Filament\Resources\ServiceCategory\Schemas\ServiceCategoriesForm;
use App\Filament\Resources\ServiceCategory\Pages\ManageServiceCategories;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
use App\Filament\Admin\Widgets\ServiceCategoryStatsWidget;

class ServiceCategoryResource extends Resource
{
    // ============================================================
    // PAGE CONFIGURATION
    // ============================================================

    // The Eloquent model this resource manages
    protected static ?string $model = ServiceCategory::class;

    // Icon to display in the sidebar (use Heroicons names)
    protected static ?string $navigationIcon = null;

    // Label for the navigation item 
    protected static ?string $navigationLabel = 'Service Categories';

    // Navigation group in the sidebar
    protected static ?string $navigationGroup = 'Job Oversight';

    // Model Label
    protected static ?string $modelLabel = 'Service Category';
    
    // Slug for the resource URLs
    protected static ?string $slug = 'jobs/service-categories';

    // Auto-refresh interval (in seconds)
    protected static int $pollingInterval = 1;

    // ============================================================
    // FORM DEFINITION
    // ============================================================
    public static function form(Form $form): Form
    {
        return ServiceCategoriesForm::configure($form);
    }

    // ============================================================
    // TABLE DEFINITION
    // ============================================================
    public static function table(Table $table): Table
    {
        return ServiceCategoriesTable::configure($table);
    }

    // ============================================================
    // PAGES
    // ============================================================
    public static function getPages(): array
    {
        return [
            'index' => ManageServiceCategories::route('/'),

            // Original generated pages
            // 'index' => Pages\ListServiceCategories::route('/'),
            // 'create' => Pages\CreateServiceCategory::route('/create'),
            // 'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
