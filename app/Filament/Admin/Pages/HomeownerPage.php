<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use App\Models\Homeowner;
use Livewire\Livewire;

class HomeownerPage extends Page implements Tables\Contracts\HasTable
{
    // Include trait that provides table handling methods (required for HasTable)
    use Tables\Concerns\InteractsWithTable;

    // =========================================================================
    // PAGE CONFIGURATION
    // =========================================================================

    // Sidebar navigation group: groups this page under "User Overview"
    protected static ?string $navigationGroup = 'User Overview';

    // Sidebar icon (null = no icon)
    protected static ?string $navigationIcon = null;

    // Sidebar label: the name displayed for the page in the navigation
    protected static ?string $navigationLabel = 'Homeowners';

    // Page title: displayed at the top of the page content
    protected static ?string $title = 'Registered Homeowners';

    // Blade view: the view used to render this page
    // IMPORTANT: must include {{ $this->table }} to render the table
    protected static string $view = 'filament.admin.pages.homeowner-page';

    // Auto-refresh interval (in seconds)
    protected static int $pollingInterval = 5;

    // =========================================================================
    // TABLE DEFINITION
    // =========================================================================

    public function table(Table $table): Table
    {
        return $table
            // -----------------------------
            // Base Query
            // -----------------------------
            // Retrieves all registered homeowners and allows optional searching
            ->query(
                Homeowner::query()
                    ->when(request('table_search'), function ($query, $search) {
                        $query->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('middle_name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%")
                              ->orWhere('address', 'like', "%{$search}%")
                              ->orWhere('city', 'like', "%{$search}%")
                              ->orWhere('region', 'like', "%{$search}%")
                              ->orWhere('postal_code', 'like', "%{$search}%");
                    })
            )

            // -----------------------------
            // Columns
            // -----------------------------
            ->columns([
                // 1. First Name Column
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                // 2. Last Name Column
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                // 3. Middle Name Column
                TextColumn::make('middle_name')
                    ->label('Middle Name')
                    ->searchable()
                    ->sortable(),

                // 4. Email Column
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                // 5. Phone Column
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),

                // 6. Address Column
                TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->sortable(),

                // 7. City Column
                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 8. Region Column
                TextColumn::make('region')
                    ->label('Region')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 9. Postal Code Column
                TextColumn::make('postal_code')
                    ->label('Postal Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 10. Status Column (with color-coded badge)
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state === 'active',
                        'danger'  => fn ($state) => $state === 'inactive',
                        'warning' => fn ($state) => $state === 'suspended',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->extraAttributes([
                        'class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs',
                    ])
                    ->sortable(),
            ])

            // -----------------------------
            // Filters
            // -----------------------------
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status') // Dropdown label
                    ->options([
                        'active'    => 'Active',
                        'inactive'  => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
            ])

            // -----------------------------
            // Row Click / Double-Click Action
            // -----------------------------
            ->recordAction('viewProfile')

            // -----------------------------
            // Row Actions
            // -----------------------------
            ->actions([
                Action::make('viewProfile')
                    ->label('') // Hidden label (triggered by row click)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('xl')
                    ->modalHeading(fn (Homeowner $record) => $record->name . ' Profile')
                    ->modalContent(fn (Homeowner $record) => view(
                        'filament.admin.pages.homeowner-profile-modal',
                        ['homeowner' => $record]
                    )),
            ])

            // -----------------------------
            // Bulk Actions
            // -----------------------------
            ->bulkActions([]); // No bulk actions enabled
    }

    // =========================================================================
    // NOTES
    // =========================================================================
    // 1. Clicking or double-clicking a row opens the homeowner profile modal.
    // 2. The View Profile icon column was removed since double-clicking handles it.
    // 3. Only safe fields are displayed; sensitive data is never exposed.
    // 4. Status uses badge colors (green=active, red=inactive, yellow=suspended).
    // 5. Polling every 5 seconds keeps the table live-updated.
    // 6. Filters allow quick status-based sorting without modifying the query.
    // 7. Column visibility toggles let admins hide less critical data.
    // 8. Modal displays homeowner details using a dedicated Blade view.
}
