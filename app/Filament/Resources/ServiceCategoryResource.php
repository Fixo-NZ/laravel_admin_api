<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Models\ServiceCategory;
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
    protected static int $pollingInterval = 5;


    // ============================================================
    // FORM DEFINITION
    // ============================================================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Details')
                    ->description('Provide the basic information for this job category.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->placeholder('e.g., Plumbing, Electrical, Carpentry')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Briefly describe what this category covers...')
                            ->rows(4)
                            ->maxLength(500)
                            ->nullable(),

                        TextInput::make('icon')
                            ->label('Icon Name')
                            ->placeholder('Enter icon filename (without .svg)')
                            ->hint('Icons are stored in storage/app/public/icons/')
                            ->suffixIcon('heroicon-o-photo')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(1),
            ]);
    }

    // ============================================================
    // TABLE DEFINITION
    // ============================================================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Icon Preview Column
                TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn($state, $record) => $state && Storage::disk('public')->exists('icons/' . $state)
                        ? new HtmlString('<img src="' . Storage::url('icons/' . $state) . '" 
                            alt="' . e($record->name) . '" class="w-6 h-6 inline-block">')
                        : new HtmlString('<span class="text-gray-400 italic">No Icon</span>')
                    )
                    ->sortable(),

                // Name Column (separate)
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),

                // Description Column (separate)
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->wrap()
                    ->sortable(),

                // Status Column
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'active',
                        'warning'  => fn($state) => strtolower($state) === 'inactive',
                        'danger' => fn($state) => strtolower($state) === 'suspended',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                // Created At
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ====================================================
            // FILTERS
            // ====================================================
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ])
                    ->searchable(),
            ])

            // ====================================================
            // TABLE ACTIONS
            // ====================================================
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading(fn($record) => 'Category Details: ' . $record->name)
                    ->modalWidth('lg')
                    ->modalContent(fn($record) => view(
                        'filament.modals.service-category-details',
                        ['category' => $record]
                    )),

                EditAction::make()
                    ->modalHeading('Edit Category')
                    ->modalWidth('lg'),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Category')
                    ->modalDescription('Are you sure you want to delete this job category? This action cannot be undone.')
                    ->color('danger'),
            ])

            // ====================================================
            // BULK ACTIONS
            // ====================================================
            ->bulkActions([])

            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->poll('30s');
    }

    // ============================================================
    // PAGES
    // ============================================================
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
