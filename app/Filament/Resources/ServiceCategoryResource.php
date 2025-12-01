<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Models\ServiceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ServiceCategoryResource extends Resource
{
    // ============================================================
    // PAGE CONFIGURATION
    // ============================================================

    protected static ?string $model = ServiceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Jobs';
    protected static ?string $navigationLabel = 'Service Categories';
    protected static ?string $modelLabel = 'Service Category';
    protected static ?string $slug = 'jobs/service-categories';

    // ============================================================
    // FORM DEFINITION
    // ============================================================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Details')
                    ->description('Provide the basic information for this job category.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Category Name')
                            ->placeholder('e.g., Plumbing, Electrical, Carpentry')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Briefly describe what this category covers...')
                            ->rows(4)
                            ->maxLength(500)
                            ->nullable(),

                        Forms\Components\TextInput::make('icon')
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
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn($state, $record) => $record->icon
                        ? new HtmlString('<img src="' . asset('storage/icons/' . $record->icon . '.svg') . '" 
                            alt="' . e($record->name) . '" class="w-6 h-6 inline-block">')
                        : new HtmlString('<span class="text-gray-400 italic">No Icon</span>')
                    )
                    ->tooltip(fn($record) => $record->icon ? $record->icon . '.svg' : 'No icon assigned')
                    ->sortable(),

                // Name Column (separate)
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),

                // Description Column (separate)
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->wrap()
                    ->sortable(),

                // Status Column
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'active',
                        'danger'  => fn($state) => strtolower($state) === 'inactive',
                        'warning' => fn($state) => strtolower($state) === 'suspended',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),

                // Created At
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ====================================================
            // FILTERS
            // ====================================================
            ->filters([
                Tables\Filters\SelectFilter::make('status')
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
                // 🟩 View Details Modal
                Tables\Actions\Action::make('view')
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

                // Tables\Actions\EditAction::make()
                //     ->modalHeading('Edit Category')
                //     ->modalWidth('lg'),

                // Tables\Actions\DeleteAction::make()
                //     ->requiresConfirmation()
                //     ->modalHeading('Delete Category')
                //     ->modalDescription('Are you sure you want to delete this job category? This action cannot be undone.')
                //     ->color('danger'),
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
