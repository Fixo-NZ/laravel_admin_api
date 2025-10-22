<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn; //outdated
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;
use Carbon\Carbon;

class AdminPage extends Page implements Tables\Contracts\HasTable
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
    protected static ?string $navigationLabel = 'Admin';

    // Page title: displayed at the top of the page content
    protected static ?string $title = 'Admin Accounts';

    // Blade view: the view used to render this page
    // IMPORTANT: must include {{ $this->table }} to render the table
    protected static string $view = 'filament.admin.pages.admin-page';

    // =========================================================================
    // TABLE DEFINITION
    // =========================================================================

    public function table(Table $table): Table
    {
        return $table
            // -----------------------------
            // Base Query
            // -----------------------------
            // Retrieve only users with role = 'admin'
            // Ensures we don’t expose non-admin users on this page
            ->query(User::query()->where('role', 'admin'))

            // -----------------------------
            // Columns
            // -----------------------------
            ->columns([
                // 1. ID Column
                TextColumn::make('id')
                    ->label('ID') // Column header
                    ->sortable()  // Users can sort by ID
                    ->toggleable(isToggledHiddenByDefault: true), // Hidden by default; can be toggled visible

                // 2. First Name Column
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(), // Allows searching for admins by first name

                // 3. Last Name Column
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(), // Allows searching for admins by last name
                
                // 4. Last Name Column
                TextColumn::make('middle_name')
                    ->label('Middle Name')
                    ->searchable()
                    ->sortable(), // Allows searching for admins by last name

                // 5. Email Column
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(), // Allows searching by email

                // 6. Status Column
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


                // 7. Created At Column
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime() // Formats timestamp as human-readable date/time
                    ->sortable() // Users can sort by creation date
                    ->toggleable(isToggledHiddenByDefault: true) // Hidden by default
                    ->extraAttributes(function ($record) {
                        // Highlight new admins (joined within last 7 days)
                        if (Carbon::parse($record->created_at)->greaterThan(Carbon::now()->subDays(7))) {
                            return ['class' => 'bg-yellow-50 font-semibold'];
                        }
                        // Highlight inactive accounts
                        if ($record->status === 'inactive') {
                            return ['class' => 'bg-red-50'];
                        }
                        return []; // Default styling
                    }),
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
                    ])
                    // The filter automatically applies a WHERE clause to the query
            ])

            // -----------------------------
            // Row Actions
            // -----------------------------
            // Disabled here (no Edit, View, Delete buttons)
            ->actions([])

            // -----------------------------
            // Bulk Actions
            // -----------------------------
            // Disabled here (no actions for multiple selected rows)
            ->bulkActions([]);
    }

    // =========================================================================
    // NOTES
    // =========================================================================
    // 1. Query Scope: Always restrict queries to necessary roles/data to prevent accidental data exposure.
    // 2. Column Visibility: Hide sensitive or rarely needed fields by default; use toggleable columns.
    // 3. Status Badges: Use colors and capitalization consistently for clarity.
    // 4. Conditional Styling: Highlight important rows (new or inactive admins) for quick recognition.
    // 5. Filters: Only expose safe, predefined options; avoid raw user input in queries.
    // 6. Actions: Row and bulk actions should be authorized and carefully controlled.
    // 7. Sorting/Search: Limit search and sort to safe fields (avoid passwords, tokens, etc.).
    // 8. Blade View: Pass only required data to views; do not expose sensitive fields.
}
