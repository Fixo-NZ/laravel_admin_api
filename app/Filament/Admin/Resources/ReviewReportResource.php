<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReviewReportResource\Pages;
use App\Filament\Admin\Resources\ReviewReportResource\RelationManagers;
use App\Models\ReviewReport;
use App\Models\Review;
use App\Models\Homeowner;
use App\Models\Tradie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewReportResource extends Resource
{
    protected static ?string $model = ReviewReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Reviews & Ratings';

    protected static ?string $navigationLabel = 'Reported Reviews';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Information')
                    ->schema([
                        Forms\Components\Select::make('review_id')
                            ->label('Review')
                            ->options(function () {
                                return Review::with(['homeowner', 'tradie'])
                                    ->get()
                                    ->mapWithKeys(function ($review) {
                                        $homeowner = $review->homeowner->first_name . ' ' . $review->homeowner->last_name;
                                        $tradie = $review->tradie->first_name . ' ' . $review->tradie->last_name;
                                        return [$review->id => "Review #{$review->id} - {$homeowner} → {$tradie} ({$review->rating}⭐)"];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn ($context) => $context === 'edit'),
                        
                        Forms\Components\Select::make('reporter_type')
                            ->label('Reporter Type')
                            ->options([
                                'App\\Models\\Homeowner' => 'Homeowner',
                                'App\\Models\\Tradie' => 'Tradie',
                            ])
                            ->required()
                            ->reactive()
                            ->disabled(fn ($context) => $context === 'edit'),
                        
                        Forms\Components\Select::make('reporter_id')
                            ->label('Reported By')
                            ->options(function (callable $get) {
                                $type = $get('reporter_type');
                                if ($type === 'App\\Models\\Homeowner') {
                                    return Homeowner::all()->mapWithKeys(function ($homeowner) {
                                        $name = trim($homeowner->first_name . ' ' . $homeowner->last_name);
                                        return [$homeowner->id => $name . ' (' . $homeowner->email . ')'];
                                    });
                                } elseif ($type === 'App\\Models\\Tradie') {
                                    return Tradie::all()->mapWithKeys(function ($tradie) {
                                        $name = trim($tradie->first_name . ' ' . $tradie->last_name);
                                        return [$tradie->id => $name . ' (' . $tradie->email . ')'];
                                    });
                                }
                                return [];
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn ($context) => $context === 'edit'),
                        
                        Forms\Components\Select::make('reason')
                            ->options([
                                'spam' => 'Spam',
                                'offensive' => 'Offensive Content',
                                'inappropriate' => 'Inappropriate',
                                'fake' => 'Fake Review',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Report Description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(fn ($context) => $context === 'edit'),
                    ])->columns(2),

                Forms\Components\Section::make('Admin Actions')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'reviewed' => 'Reviewed',
                                'resolved' => 'Resolved',
                                'dismissed' => 'Dismissed',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Internal notes about how this report was handled'),
                    ]),
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
                
                Tables\Columns\TextColumn::make('review_id')
                    ->label('Review ID')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => 'Rating: ' . $record->review->rating . '⭐'),
                
                Tables\Columns\TextColumn::make('review_author')
                    ->label('Review Author')
                    ->formatStateUsing(function ($record) {
                        $homeowner = $record->review->homeowner;
                        return trim($homeowner->first_name . ' ' . $homeowner->last_name);
                    })
                    ->description(fn ($record) => $record->review->homeowner->email)
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('review_tradie')
                    ->label('Tradie')
                    ->formatStateUsing(function ($record) {
                        $tradie = $record->review->tradie;
                        return trim($tradie->first_name . ' ' . $tradie->last_name);
                    })
                    ->description(fn ($record) => $record->review->tradie->business_name ?? $record->review->tradie->email)
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('reporter')
                    ->label('Reported By')
                    ->formatStateUsing(function ($record) {
                        if ($record->reporter_type === 'App\\Models\\Homeowner') {
                            $reporter = Homeowner::find($record->reporter_id);
                            $type = '(Homeowner)';
                        } else {
                            $reporter = Tradie::find($record->reporter_id);
                            $type = '(Tradie)';
                        }
                        if ($reporter) {
                            return trim($reporter->first_name . ' ' . $reporter->last_name) . ' ' . $type;
                        }
                        return 'Unknown';
                    })
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('reason')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'spam' => 'warning',
                        'offensive' => 'danger',
                        'inappropriate' => 'danger',
                        'fake' => 'warning',
                        'other' => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
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
                        'pending' => 'warning',
                        'reviewed' => 'info',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reported At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reason')
                    ->options([
                        'spam' => 'Spam',
                        'offensive' => 'Offensive Content',
                        'inappropriate' => 'Inappropriate',
                        'fake' => 'Fake Review',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'resolved' => 'Resolved',
                        'dismissed' => 'Dismissed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('view_review')
                    ->label('View Review')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (ReviewReport $record): string => route('filament.admin.resources.reviews.edit', ['record' => $record->review_id]))
                    ->openUrlInNewTab(),
                
                Tables\Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Resolution Notes')
                            ->required()
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('hide_review')
                            ->label('Hide the reported review')
                            ->default(false),
                    ])
                    ->action(function (ReviewReport $record, array $data) {
                        $record->update([
                            'status' => 'resolved',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                        
                        if ($data['hide_review']) {
                            $record->review->update(['status' => 'hidden']);
                        }
                    })
                    ->visible(fn (ReviewReport $record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Dismissal Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (ReviewReport $record, array $data) {
                        $record->update([
                            'status' => 'dismissed',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                    })
                    ->visible(fn (ReviewReport $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_reviewed')
                        ->label('Mark as Reviewed')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'reviewed'])),
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
            'index' => Pages\ListReviewReports::route('/'),
            'create' => Pages\CreateReviewReport::route('/create'),
            'edit' => Pages\EditReviewReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}