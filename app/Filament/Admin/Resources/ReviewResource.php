<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReviewResource\Pages;
use App\Models\Homeowner;
use App\Models\Tradie;
use App\Filament\Admin\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Reviews & Ratings';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Review Information')
                    ->schema([
                        Forms\Components\Select::make('job_id')
                            ->relationship('job', 'id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                    Forms\Components\Select::make('homeowner_id')
                        ->relationship('homeowner', 'first_name')
                        ->label('Customer (Homeowner)')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name . ' (' . $record->email . ')'),

                    Forms\Components\Select::make('tradie_id')
                        ->relationship('tradie', 'first_name')
                        ->label('Service Provider (Tradie)')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name . ' (' . $record->email . ')'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'reported' => 'Reported',
                                'hidden' => 'Hidden',
                            ])
                            ->default('approved')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Main Rating')
                    ->schema([
                        Forms\Components\Select::make('rating')
                            ->label('Overall Rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->required(),
                        
                        Forms\Components\Textarea::make('feedback')
                            ->label('Customer Feedback')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Detailed Ratings')
                    ->schema([
                        Forms\Components\Select::make('service_quality_rating')
                            ->label('Service Quality')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ]),
                        
                        Forms\Components\Textarea::make('service_quality_comment')
                            ->label('Service Quality Comment')
                            ->rows(2),
                        
                        Forms\Components\Select::make('performance_rating')
                            ->label('Performance')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ]),
                        
                        Forms\Components\Textarea::make('performance_comment')
                            ->label('Performance Comment')
                            ->rows(2),
                        
                        Forms\Components\Select::make('contractor_service_rating')
                            ->label('Contractor Service')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ]),
                        
                        Forms\Components\Select::make('response_time_rating')
                            ->label('Response Time')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ]),
                        
                        Forms\Components\TextInput::make('best_feature')
                            ->label('Best Feature')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Settings')
                    ->schema([
                        Forms\Components\Toggle::make('show_username')
                            ->label('Show Username on Review')
                            ->default(true),
                        
                        Forms\Components\TextInput::make('helpful_count')
                            ->label('Helpful Count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                
Tables\Columns\TextColumn::make('homeowner')
                    ->label('Customer')
                    ->formatStateUsing(function ($record) {
                        $name = trim($record->homeowner->first_name . ' ' . $record->homeowner->last_name);
                        return $name;
                    })
                    ->description(fn ($record) => $record->homeowner->email)
                    ->sortable()
                    ->searchable(['homeowners.first_name', 'homeowners.last_name', 'homeowners.email']),
                
                Tables\Columns\TextColumn::make('tradie')
                    ->label('Tradie')
                    ->formatStateUsing(function ($record) {
                        $name = trim($record->tradie->first_name . ' ' . $record->tradie->last_name);
                        return $name;
                    })
                    ->description(fn ($record) => $record->tradie->business_name ?? $record->tradie->email)
                    ->sortable()
                    ->searchable(['tradies.first_name', 'tradies.last_name', 'tradies.email']),
                
                
                Tables\Columns\TextColumn::make('job_id')
                    ->label('Job ID')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => $state . ' ⭐')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('feedback')
                    ->label('Feedback')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'reported' => 'danger',
                        'hidden' => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('helpful_count')
                    ->label('Helpful')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'reported' => 'Reported',
                        'hidden' => 'Hidden',
                    ]),
                
                Tables\Filters\SelectFilter::make('tradie_id')
                    ->relationship('tradie', 'first_name')
                    ->label('Tradie')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => 'approved']))
                    ->visible(fn (Review $record) => $record->status !== 'approved'),
                
                Tables\Actions\Action::make('hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => 'hidden']))
                    ->visible(fn (Review $record) => $record->status !== 'hidden'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'approved'])),
                    
                    Tables\Actions\BulkAction::make('hide')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'hidden'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}