<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Models\Tradie;
use Illuminate\Database\Eloquent\Builder;

class TradieVerification extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Verifications';
    protected static ?string $title = 'Tradie Verifications';

    // ✅ Puts it in the sidebar group with Complaints
    protected static \UnitEnum|string|null $navigationGroup = 'Moderation';

    // ✅ Sidebar order
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.tradie-verification';

    protected function getTableQuery(): Builder
    {
        return Tradie::query()
            ->where('status', 'pending');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->searchable(),
                TextColumn::make('last_name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('business_name')->label('Business'),
                TextColumn::make('city'),
                TextColumn::make('region'),
                TextColumn::make('created_at')->label('Registered')->dateTime(),
            ])
            ->actions([
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Tradie $record) {
                        $record->update([
                            'status' => 'active',
                            'verified_at' => now(),
                        ]);
                    }),
                Action::make('view')
                    ->label('View')
                    ->modalHeading('Tradie Details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ]);
    }
}
