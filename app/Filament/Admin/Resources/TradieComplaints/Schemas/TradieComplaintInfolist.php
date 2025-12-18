<?php

namespace App\Filament\Admin\Resources\TradieComplaints\Schemas;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class TradieComplaintInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tradie.full_name')
                    ->label('Complaint Against:'),

                TextEntry::make('homeowner.full_name')
                    ->label('Complaint By:')
                    ->placeholder('—'),

                TextEntry::make('title')
                    ->label('Complaint Reason'),

                TextEntry::make('status')
                    ->label('Status')
                    ->badge(),

                TextEntry::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime()
                    ->placeholder('Not reviewed'),

                TextEntry::make('created_at')
                    ->label('Reported At')
                    ->dateTime(),

                TextEntry::make('description')
                    ->label('Complaint Description')
                    ->formatStateUsing(fn($state) => nl2br(e($state)))
                    ->html(),

                Actions::make([
                    self::suspendAction(),
                    self::unsuspendAction(),
                ])->columnSpanFull(),
            ]);
    }

    protected static function suspendAction(): Action
    {
        return Action::make('suspend_tradie')
            ->label('Suspend Tradie')
            ->color('danger')
            ->icon('heroicon-o-pause')
            ->visible(fn($record) => $record->tradie?->status !== 'suspended')
            ->form([
                Forms\Components\Textarea::make('suspension_reason')
                    ->label('Reason for Suspension')
                    ->required()
                    ->rows(3),

                Forms\Components\Select::make('suspension_duration')
                    ->label('Duration')
                    ->options([
                        '3 days' => '3 Days',
                        '1 week' => '1 Week',
                        '2 weeks' => '2 Weeks',
                        '1 month' => '1 Month',
                        'custom' => 'Custom Date',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\DatePicker::make('custom_end_date')
                    ->label('Custom End Date')
                    ->visible(fn($get) => $get('suspension_duration') === 'custom')
                    ->required(fn($get) => $get('suspension_duration') === 'custom')
                    ->minDate(now()),
            ])
            ->action(function (array $data, $record) {

                $endDate = match ($data['suspension_duration']) {
                    '3 days' => now()->addDays(3),
                    '1 week' => now()->addWeek(),
                    '2 weeks' => now()->addWeeks(2),
                    '1 month' => now()->addMonth(),
                    'custom' => Carbon::parse($data['custom_end_date']),
                };

                $record->tradie()->update([
                    'status' => 'suspended',
                    'suspension_reason' => $data['suspension_reason'],
                    'suspension_start' => now(),
                    'suspension_end' => $endDate,
                ]);

                Notification::make()
                    ->title('Tradie Suspended')
                    ->body("{$record->tradie->full_name} suspended until {$endDate->format('M d, Y')}")
                    ->danger()
                    ->send();
            });
    }

    protected static function unsuspendAction(): Action
    {
        return Action::make('unsuspend_tradie')
            ->label('Release Suspension')
            ->color('success')
            ->icon('heroicon-o-play')
            ->requiresConfirmation()
            ->visible(fn($record) => $record->tradie?->status === 'suspended')
            ->action(function ($record) {
                $tradie = $record->tradie;

                $tradie->update([
                    'status' => 'active',
                    'suspension_reason' => null,
                    'suspension_start' => null,
                    'suspension_end' => null,
                ]);

                Notification::make()
                    ->title('Tradie Unsuspended')
                    ->body("{$tradie->full_name} is now active")
                    ->success()
                    ->send();
            });
    }
}
