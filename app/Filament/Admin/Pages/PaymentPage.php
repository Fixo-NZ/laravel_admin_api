<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use App\Models\Payment;

class PaymentPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    // ===============================
    // NAVIGATION / PAGE CONFIGURATION
    // ===============================
    protected static ?string $navigationGroup = 'Payments';
    protected static ?string $navigationLabel = 'Payment';
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Payments';
    protected static bool $shouldRegisterNavigation = true;
    protected static string $view = 'filament.admin.pages.payment-page';

    // ===============================
    // TABLE CONFIGURATION
    // ===============================
    public function table(Table $table): Table
    {
        return $table
            ->query(Payment::query()->with('homeowner')) // Eager load homeowner

            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make(name: 'homeowner.first_name')->label('Homeowner')->sortable()->searchable(),
                TextColumn::make('amount')->label('Amount')->sortable(),
                TextColumn::make('currency')->label('Currency')->sortable(),
                TextColumn::make('card_brand')->label('Card Brand')->toggleable(),
                TextColumn::make('card_last4number')->label('Last 4')->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => $state === 'succeeded',
                        'danger' => fn($state) => $state === 'failed',
                        'warning' => fn($state) => $state === 'pending',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable()->toggleable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'pending' => 'Pending',
                        'succeeded' => 'Succeeded',
                        'failed' => 'Failed',
                    ]),
            ])

            

            ->bulkActions([]);
    }
}
