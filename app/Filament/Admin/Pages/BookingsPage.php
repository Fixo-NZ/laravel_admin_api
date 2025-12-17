<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Booking;

class BookingsPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.admin.pages.bookings-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()->with(['homeowner', 'tradie', 'service'])
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('homeowner.id')->label('Homeowner ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('homeowner.first_name')->label('Homeowner')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tradie.id')->label('Tradie ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tradie.first_name')->label('Tradie')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('service.id')->label('Service ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('service.job_description')->label('Service')->limit(80)->wrap()->searchable(),
                TextColumn::make('booking_start')->label('Start')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('booking_end')->label('End')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => in_array(strtolower($state), ['confirmed','completed']),
                        'danger' => fn ($state) => in_array(strtolower($state), ['canceled','cancelled']),
                        'warning' => fn ($state) => strtolower($state) === 'pending',
                    ])
                    ->sortable(),
                TextColumn::make('total_price')->label('Price')->money('usd', true)->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
