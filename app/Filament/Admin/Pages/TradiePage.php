<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\Tradie;

class TradiePage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    // =========================================================================
    // PAGE CONFIGURATION
    // =========================================================================
    protected static \UnitEnum|string|null $navigationGroup = 'User Overview';
    protected static \BackedEnum|string|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Tradies';
    protected static ?string $title = 'Registered Tradies';
    protected string $view = 'filament.admin.pages.tradie-page';
    protected static int $pollingInterval = 5;

    // =========================================================================
    // TABLE DEFINITION
    // =========================================================================
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tradie::query()
                    ->when(request('table_search'), function ($query, $search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('region', 'like', "%{$search}%")
                            ->orWhere('postal_code', 'like', "%{$search}%")
                            // ->orWhere('trade_type', 'like', "%{$search}%")
                            ->orWhere('availability_status', 'like', "%{$search}%");
                    })
            )
            ->columns([
                TextColumn::make('first_name')->label('First Name')->searchable()->sortable(),
                TextColumn::make('last_name')->label('Last Name')->searchable()->sortable(),
                TextColumn::make('middle_name')->label('Middle Name')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('phone')->label('Phone')->searchable()->sortable(),
                TextColumn::make('address')->label('Address')->searchable()->sortable(),
                TextColumn::make('city')->label('City')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')->label('Region')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('postal_code')->label('Postal Code')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),

                // Reordered columns
                TextColumn::make('availability_status')
                    ->label('Availability')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'available',
                        'danger'  => fn($state) => strtolower($state) === 'unavailable',
                        'warning' => fn($state) => strtolower($state) === 'busy',
                        'secondary' => fn($state) => !in_array(strtolower($state), ['available', 'unavailable', 'busy']),
                    ])
                    ->formatStateUsing(fn($state) => $state ? ucfirst($state) : 'Unavailable')
                    ->extraAttributes([
                        'class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs'
                    ])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => $state === 'active',
                        'danger' => fn($state) => $state === 'inactive',
                        'warning' => fn($state) => $state === 'suspended',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->extraAttributes(['class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs'])
                    ->sortable(),

                // TextColumn::make('trade_type')->label('Trade Type')->searchable()->sortable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
                SelectFilter::make('availability_status')
                    ->label('Filter by Availability')
                    ->options([
                        'available' => 'Available',
                        'busy' => 'Busy',
                        'unavailable' => 'Unavailable',
                    ]),
            ])
            ->recordAction('viewProfile')
            ->recordActions([
                Action::make('viewProfile')
                    ->label('')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('xl')
                    ->modalHeading(fn(Tradie $record) => $record->name . ' Profile')
                    ->modalContent(fn(Tradie $record) => view(
                        'filament.admin.pages.tradie-profile-modal',
                        ['tradie' => $record]
                    ))
                    ->modalActions([
                        Action::make('suspend')
                            ->label('Suspend')
                            ->color('danger')
                            ->icon('heroicon-o-pause')
                            ->requiresConfirmation()
                            ->visible(fn(Tradie $record) => $record->status !== 'suspended')
                            ->action(function (Tradie $record, $livewire) {
                                $record->update(['status' => 'suspended']);

                                \Filament\Notifications\Notification::make()
                                    ->title("{$record->first_name} {$record->last_name} has been suspended.")
                                    ->danger()
                                    ->send();

                                $livewire->dispatch('close-modal', id: 'viewProfile');
                                $livewire->dispatch('refresh');
                            }),

                        Action::make('unsuspend')
                            ->label('Unsuspend')
                            ->color('success')
                            ->icon('heroicon-o-play')
                            ->requiresConfirmation()
                            ->visible(fn(Tradie $record) => $record->status === 'suspended')
                            ->action(function (Tradie $record, $livewire) {
                                $record->update(['status' => 'active']);

                                \Filament\Notifications\Notification::make()
                                    ->title("{$record->first_name} {$record->last_name} has been unsuspended.")
                                    ->success()
                                    ->send();

                                $livewire->dispatch('close-modal', id: 'viewProfile');
                                $livewire->dispatch('refresh');
                            }),

                        Action::make('setHourlyRate')
                            ->label('Set Hourly Rate')
                            ->color('info')
                            ->icon('heroicon-o-currency-dollar')
                            ->visible(fn(Tradie $record) => true)
                            ->form([
                                \Filament\Forms\Components\Select::make('hourly_rate')
                                    ->label('Select Fair Hourly Rate')
                                    ->options(function (Tradie $record) {
                                        $years = $record->years_experience;

                                        // rate logic based on experience
                                        if ($years >= 15) {
                                            return [
                                                1200 => '₱1,200/hr (Expert, 15+ yrs)',
                                                1500 => '₱1,500/hr (Master Level)',
                                            ];
                                        } elseif ($years >= 10) {
                                            return [
                                                800 => '₱800/hr (10–14 yrs)',
                                                1000 => '₱1,000/hr (Senior Level)',
                                            ];
                                        } elseif ($years >= 5) {
                                            return [
                                                500 => '₱500/hr (5–9 yrs)',
                                                700 => '₱700/hr (Mid Level)',
                                            ];
                                        } elseif ($years >= 2) {
                                            return [
                                                300 => '₱300/hr (2–4 yrs)',
                                                400 => '₱400/hr (Junior Level)',
                                            ];
                                        } else {
                                            return [
                                                150 => '₱150/hr (Beginner, <2 yrs)',
                                                200 => '₱200/hr (Entry Level)',
                                            ];
                                        }
                                    })
                                    ->required(),
                            ])
                            ->action(function (array $data, Tradie $record, $livewire) {
                                $record->update(['hourly_rate' => $data['hourly_rate']]);

                                \Filament\Notifications\Notification::make()
                                    ->title("Hourly rate for {$record->first_name} updated to ₱{$data['hourly_rate']}/hr.")
                                    ->success()
                                    ->send();

                                $livewire->dispatch('close-modal', id: 'viewProfile');
                                $livewire->dispatch('refresh');
                            }),
                    ])
            ])
            ->bulkActions([]);
    }

    // =========================================================================
    // NOTES
    // =========================================================================
    // 1. Clicking or double-clicking a row opens the tradie profile modal.
    // 2. The View Profile icon column was removed since double-clicking handles it.
    // 3. Only safe fields are displayed; sensitive data is never exposed.
    // 4. Status uses badge colors (green=active, red=inactive, yellow=suspended).
    // 5. Availability uses badge colors (green=available, red=unavailable, yellow=busy).
    // 6. Polling every 5 seconds keeps the table live-updated.
    // 7. Filters allow quick status-based sorting without modifying the query.
    // 8. Column visibility toggles let admins hide less critical data.
    // 9. Modal displays tradie details using a dedicated Blade view.
    // 10. Trade Type helps identify each tradie’s specialization or profession.
}
