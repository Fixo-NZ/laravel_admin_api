<?php

namespace App\Filament\Admin\Pages;

use App\Models\Tradie;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;

class TradiePage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationGroup = 'User Overview';
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Tradies';
    protected static ?string $title = 'Registered Tradies';
    protected static string $view = 'filament.admin.pages.tradie-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\TradieStatsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tradie::query()
                    ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
                    ->when(request('availability_status'), fn ($query, $availability) => $query->where('availability_status', $availability))
            )
            ->poll('5s')
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('middle_name')
                    ->label('Middle Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('region')
                    ->label('Region')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('postal_code')
                    ->label('Postal Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('trade_type')
                    ->label('Trade Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('availability_status')
                    ->label('Availability')
                    ->badge()
                    ->colors([
                        'success'   => fn ($state) => strtolower((string) $state) === 'available',
                        'warning'   => fn ($state) => strtolower((string) $state) === 'busy',
                        'danger'    => fn ($state) => strtolower((string) $state) === 'unavailable',
                        'secondary' => fn ($state) => ! in_array(strtolower((string) $state), ['available', 'busy', 'unavailable']),
                    ])
                    ->formatStateUsing(fn ($state) => $state ? ucfirst((string) $state) : 'Unavailable')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state === 'active',
                        'danger'  => fn ($state) => $state === 'inactive',
                        'warning' => fn ($state) => $state === 'suspended',
                        'info'    => fn ($state) => $state === 'pending',
                    ])
                    ->formatStateUsing(fn ($state) => $state ? ucfirst((string) $state) : '-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'active'    => 'Active',
                        'inactive'  => 'Inactive',
                        'suspended' => 'Suspended',
                        'pending'   => 'Pending',
                    ]),

                SelectFilter::make('availability_status')
                    ->label('Filter by Availability')
                    ->options([
                        'available'   => 'Available',
                        'busy'        => 'Busy',
                        'unavailable' => 'Unavailable',
                    ]),
            ])
            // clicking row opens this action (modal)
            ->recordAction('viewProfile')
            ->actions([
                Action::make('viewProfile')
                    ->label('')
                    ->icon('heroicon-m-eye')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('xl')
                    ->modalHeading(fn (Tradie $record) => trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? '')) . ' Profile')
                    ->modalContent(fn (Tradie $record) => view(
                        'filament.admin.pages.tradie-profile-modal',
                        ['tradie' => $record]
                    )),
            ])
            ->bulkActions([]);
    }
}
