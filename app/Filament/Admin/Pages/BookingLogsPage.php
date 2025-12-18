<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\BookingLog;

class BookingLogsPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    // Page config
    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Booking Logs';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.booking-logs-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BookingLog::query()->with(['booking'])
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('booking.id')->label('Booking ID')->sortable(),
                TextColumn::make('user_id')->label('User ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('action')->label('Action')->searchable()->sortable(),
                TextColumn::make('notes')->label('Notes')->limit(80)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Action')
                    ->options(BookingLog::query()->select('action')->distinct()->pluck('action','action')->toArray()),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
