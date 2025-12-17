<?php

namespace App\Filament\Resources\ServiceCategory\Tables;

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

class ServiceCategoriesTable
{
    public static function configure(Table $table): Table
    {
         return $table
            ->columns([
                // Icon Preview Column
                TextColumn::make('icon')
                    ->label('Image')
                    ->formatStateUsing(fn($state, $record) => $record->icon
                        ? new HtmlString('<img src="' . asset('storage/icons/' . $record->icon . '.svg') . '" 
                            alt="' . e($record->name) . '" class="w-6 h-6 inline-block">')
                        : new HtmlString('<span class="text-gray-400 italic">No Icon</span>')
                    )
                    // ->
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
    
}
