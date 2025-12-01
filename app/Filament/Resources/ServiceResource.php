<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
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
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Service Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(7)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->createOptionModalHeading('Add New Category')
                    ->columnSpanFull(),
            ]);
    }

    // ============================================================
    // TABLE DEFINITION
    // ============================================================   
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('name')
                    ->label('Service Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'active',
                        'warning'  => fn($state) => strtolower($state) === 'inactive',
                        'danger' => fn($state) => strtolower($state) === 'suspended',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->extraAttributes([
                        'class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs',
                    ])
                    ->sortable(),

                    TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                SelectFilter::make('category')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ])
            ->recordAction('viewDetails')
            ->actions([
                // Custom modal: View Details
                Action::make('viewDetails')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading(fn($record) => 'Service Details: ' . $record->name)
                    ->modalWidth('lg')
                    ->modalContent(fn($record) => view(
                        'filament.modals.service-details',
                        ['service' => $record]
                    )),

                // Edit action (only through action button)
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('Edit Service')
                    ->modalSubmitActionLabel('Save Changes')
                    ->modalWidth('lg'),


                DeleteAction::make()
                    ->modalHeading('Delete Service')
                    ->modalDescription('Are you sure you want to delete this service?'),
            ])

            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
