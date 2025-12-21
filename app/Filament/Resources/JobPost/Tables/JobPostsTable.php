<?php

namespace App\Filament\Resources\JobPost\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class JobPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table

        /* ======================================================
         | Columns
         ====================================================== */
        ->columns([

            TextColumn::make('number')
                ->label('Number')
                ->sortable()
                ->searchable(),

            TextColumn::make('title')
                ->label('Title')
                ->sortable()
                ->searchable()
                ->limit(25),

            TextColumn::make('homeowner.full_name')
                ->label('Homeowner')
                ->sortable()
                ->toggleable(),

            TextColumn::make('category.name')
                ->label('Category')
                ->sortable()
                ->toggleable(),

            TextColumn::make('services_count')
                ->label('Services')
                ->counts('services')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('job_type')
                ->badge()
                ->colors([
                    'success' => 'standard',
                    'danger'  => 'urgent',
                    'info'    => 'recurrent',
                ])
                ->formatStateUsing(fn ($state) => ucfirst($state))
                ->sortable(),

            TextColumn::make('job_size')
                ->badge()
                ->colors([
                    'info'    => 'small',
                    'warning' => 'medium',
                    'success' => 'large',
                ])
                ->formatStateUsing(fn ($state) => ucfirst($state))
                ->sortable(),

            TextColumn::make('budget')
                ->money('PHP')
                ->label('Budget')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('status')
                ->badge()
                ->colors([
                    'success' => 'open',
                    'info'    => 'assigned',
                    'warning' => 'in_progress',
                    'primary' => 'completed',
                    'danger'  => 'cancelled',
                    'gray'    => 'expired',
                ])
                ->formatStateUsing(fn ($state) =>
                    Str::of($state)->replace('_', ' ')->title()
                )
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Posted')
                ->date()
                ->sortable(),
        ])

        /* ======================================================
         | Filters
         ====================================================== */
        ->filters([

            TrashedFilter::make(),

            SelectFilter::make('status')
                ->options([
                    'open' => 'Open',
                    'assigned' => 'Assigned',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                    'expired' => 'Expired',
                ]),

            SelectFilter::make('job_type')
                ->options([
                    'standard' => 'Standard',
                    'urgent' => 'Urgent',
                    'recurrent' => 'Recurrent',
                ]),

            SelectFilter::make('job_size')
                ->options([
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large',
                ]),

            SelectFilter::make('service_category_id')
                ->label('Category')
                ->relationship('category', 'name'),

            Filter::make('created_at')
                ->label('Created Date')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from'),
                    \Filament\Forms\Components\DatePicker::make('to'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['from'] ?? null) {
                        $indicators['from'] = 'From ' . Carbon::parse($data['from'])->toFormattedDateString();
                    }

                    if ($data['to'] ?? null) {
                        $indicators['to'] = 'Until ' . Carbon::parse($data['to'])->toFormattedDateString();
                    }

                    return $indicators;
                }),

            Filter::make('stale')
                ->label('Open > 30 Days')
                ->query(fn (Builder $query) =>
                    $query->where('status', 'open')
                          ->where('created_at', '<', now()->subDays(30))
                ),
        ])

        /* ======================================================
         | Row Actions
         ====================================================== */
        ->actions([

            ViewAction::make()->modalWidth('4xl'),

            EditAction::make(),

            DeleteAction::make(),
        ])

        /* ======================================================
         | Bulk Actions
         ====================================================== */
        ->bulkActions([
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])

        /* ======================================================
         | Grouping
         ====================================================== */
        ->groups([
            Group::make('created_at')
                ->label('Job Date')
                ->date()
                ->collapsible(),
        ]);
    }
}
