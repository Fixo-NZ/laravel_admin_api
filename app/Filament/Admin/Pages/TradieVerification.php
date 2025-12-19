<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Models\Tradie;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;

class TradieVerification extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Verifications';
    protected static ?string $title = 'Tradie Verifications';

    protected static \UnitEnum|string|null $navigationGroup = 'Moderation';

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
                Action::make('view')
                    ->label('View & Verify')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Tradie Verification')
                    ->modalWidth('xl')
                    ->form([
                        Placeholder::make('name')
                            ->label('Name')
                            ->content(
                                fn(Tradie $record) =>
                                "{$record->first_name} {$record->last_name}"
                            ),

                        Placeholder::make('email')
                            ->content(fn(Tradie $record) => $record->email),

                        Placeholder::make('business')
                            ->label('Business Name')
                            ->content(fn(Tradie $record) => $record->business_name),

                        Placeholder::make('location')
                            ->label('Location')
                            ->content(
                                fn(Tradie $record) =>
                                "{$record->city}, {$record->region}"
                            ),

                        Placeholder::make('registered')
                            ->label('Registered At')
                            ->content(
                                fn(Tradie $record) =>
                                $record->created_at->format('d M Y H:i')
                            ),
                    ])
                    ->modalActions([
                        Action::make('verify')
                            ->label('Verify Tradie')
                            ->color('success')
                            ->icon('heroicon-o-check')
                            ->requiresConfirmation()
                            ->action(function (Tradie $record) {
                                $record->update([
                                    'status' => 'active',
                                    'verified_at' => now(),
                                ]);
                            }),

                        Action::make('reject')
                            ->label('Reject')
                            ->color('danger')
                            ->icon('heroicon-o-x-mark')
                            ->form([
                                Forms\Components\Textarea::make('rejection_reason')
                                    ->label('Reason for Rejection')
                                    ->required()
                                    ->maxLength(1000),
                            ])
                            ->requiresConfirmation()
                            ->action(function (array $data, Tradie $record) {
                                $record->update([
                                    'status' => 'rejected',
                                    'rejected_at' => now(),
                                    'rejection_reason' => $data['rejection_reason'],
                                    'rejected_by' => Auth::id(),
                                ]);
                            }),

                    ]),
            ])
            ->recordAction('view');
    }
}
