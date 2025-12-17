<?php

namespace App\Filament\Resources\Service\Tables;

use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;
use Filament\Tables;
use Filament\Tables\Columns;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;

class ServicesTable
{
<<<<<<< HEAD:app/Filament/Resources/ServiceResource.php
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
=======
    public static function configure(Table $table): Table
>>>>>>> origin/g2/job_posting:app/Filament/Resources/Service/Tables/ServicesTable.php
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
                    ->label('')
<<<<<<< HEAD:app/Filament/Resources/ServiceResource.php
                    ->icon(null)
=======
                    ->icon('heroicon-o-eye')
>>>>>>> origin/g2/job_posting:app/Filament/Resources/Service/Tables/ServicesTable.php
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalHeading(fn($record) => 'Service Details: ' . $record->name)
                    ->modalWidth('lg')
                    ->modalContent(fn($record) => view(
                        'filament.modals.service-details',
                        ['service' => $record]
                    )),


                // Edit act   

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
    
}
