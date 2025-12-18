<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class HighValuePayments extends TableWidget
{
    protected static ?string $heading = 'High-Value Payments';
    protected static ?int $sort = 4;

    protected function getTableQuery(): Builder
    {
        return Payment::query()
            ->where('status', 'succeeded')
            ->where('amount', '>=', 1000)
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('amount')->label('Amount')->sortable(),
            TextColumn::make('currency')->label('Currency')->sortable(),
            TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
        ];
    }
}
