<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\JobRequest;

class JobRequestsPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Job Requests';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.admin.pages.job-requests-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobRequest::query()->with(['homeowner'])
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('job_category_id')->label('Category')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('homeowner.first_name')->label('Homeowner')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')->label('Title')->searchable()->sortable(),
                TextColumn::make('job_type')->label('Type')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->label('Status')->sortable()->badge(),
                TextColumn::make('budget')->label('Budget')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scheduled_at')->label('Scheduled')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')->label('Location')->limit(80)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'assigned' => 'Assigned',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('job_type')
                    ->label('Job Type')
                    ->options([
                        'urgent' => 'Urgent',
                        'standard' => 'Standard',
                        'recurring' => 'Recurring',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
