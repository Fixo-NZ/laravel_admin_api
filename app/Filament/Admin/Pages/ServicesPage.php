<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Service;

class ServicesPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Services';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.admin.pages.services-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Service::query()->with(['homeowner'])
            )
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('homeowner_id')->label('Homeowner ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('homeowner.first_name')->label('Homeowner')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('job_categoryid')->label('Job Category')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('job_description')->label('Description')->limit(120)->wrap()->searchable(),
                TextColumn::make('location')->label('Location')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->label('Status')->sortable()->searchable(),
                TextColumn::make('rating')->label('Rating')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'InProgress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
