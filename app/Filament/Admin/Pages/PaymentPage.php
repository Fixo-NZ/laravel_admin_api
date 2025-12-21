<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\HighValuePayments;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Payment;
use App\Models\Homeowner;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Admin\Widgets\PaymentStatsWidget;
use App\Filament\Admin\Widgets\PaymentStatusChart;
use App\Filament\Admin\Widgets\PaymentRevenueChart;

class PaymentPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    // ===============================
    // NAVIGATION / PAGE CONFIGURATION
    // ===============================
    protected static ?string $navigationGroup = 'Payments Management';
    protected static ?string $navigationLabel = 'Payments';
    protected static ?string $navigationIcon = null;
    protected static ?string $title = 'Payments';
    protected static bool $shouldRegisterNavigation = true;
    protected static string $view = 'filament.admin.pages.payment-page';

    // ===============================
    // HEADER WIDGETS
    // ===============================
    protected function getHeaderWidgets(): array
    {
        return [
            PaymentStatsWidget::class,
            PaymentStatusChart::class,
            HighValuePayments::class,
            PaymentRevenueChart::class,
        ];
    }

    // ===============================
    // TABLE CONFIGURATION
    // ===============================
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()->with('homeowner')
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('homeowner.first_name')
                    ->label('Homeowner')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Homeowner::select('first_name')
                                ->whereColumn('homeowners.id', 'payments.homeowner_id'),
                            $direction
                        );
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Currency')
                    ->sortable(),

                TextColumn::make('card_brand')
                    ->label('Card Brand')
                    ->toggleable(),

                TextColumn::make('card_last4number')
                    ->label('Last 4')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state === 'succeeded',
                        'danger'  => fn ($state) => $state === 'failed',
                        'warning' => fn ($state) => $state === 'pending',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'pending'   => 'Pending',
                        'succeeded' => 'Succeeded',
                        'failed'    => 'Failed',
                    ]),
            ])
            ->bulkActions([]);
    }
}
