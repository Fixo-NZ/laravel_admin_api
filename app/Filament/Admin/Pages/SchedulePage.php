<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use App\Models\HomeownerJobOffer;

class SchedulePage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    // Sidebar navigation
    protected static ?string $navigationGroup = 'User Overview';
    protected static ?string $navigationLabel = 'Schedules';
    protected static ?string $title = 'Calendar schedules';
    protected static ?string $navigationIcon = null;

    protected static string $view = 'filament.admin.pages.homeowner-job-offer-page';
    protected static int $pollingInterval = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(HomeownerJobOffer::query()->with(['homeowner', 'tradie', 'category']))

            ->columns([

                TextColumn::make('title')
                    ->label('Job Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('homeowner.first_name')
                    ->label('Homeowner')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->homeowner?->first_name . ' ' . $record->homeowner?->last_name
                    )
                    ->searchable(),

                TextColumn::make('tradie.first_name')
                    ->label('Tradie')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->tradie
                            ? $record->tradie->first_name . ' ' . $record->tradie->last_name
                            : 'Not Assigned'
                    )
                    ->sortable(),

                TextColumn::make('job_type')
                    ->label('Job Type')
                    ->sortable(),

                TextColumn::make('preferred_date')
                    ->label('Preferred Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger'  => 'cancelled',
                        'info'    => 'completed',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Address')
                    ->limit(15)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->recordAction('viewDetails')

            ->actions([
                Action::make('viewDetails')
                    ->label('View Details')
                    ->modalHeading(fn (HomeownerJobOffer $record) => $record->title . ' - Job Details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('4xl')
                    ->modalContent(fn (HomeownerJobOffer $record) => view(
                        'filament.admin.pages.job-offer-details-modal',
                        ['offer' => $record]
                    )),
            ])

            ->bulkActions([]);
    }
}
