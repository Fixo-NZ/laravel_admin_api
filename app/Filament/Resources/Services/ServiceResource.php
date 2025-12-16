<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Jobs & Services';

    protected static ?string $navigationLabel = 'Jobs';

    protected static ?string $modelLabel = 'Service';

    protected static ?string $slug = 'jobs/job-categories';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('homeowner_id')
                    ->label('Homeowner')
                    ->relationship('homeowner', 'email')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('job_categoryid')
                    ->label('Job Category')
                    ->relationship('category', 'category_name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('job_description')
                    ->label('Job Description')
                    ->required()
                    ->rows(5)
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('location')
                    ->label('Location')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'InProgress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('Pending'),
                Forms\Components\TextInput::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('homeowner.email')->label('Homeowner')->searchable()->sortable(),
                TextColumn::make('category.category_name')->label('Category')->searchable()->sortable(),
                TextColumn::make('job_description')->label('Job Description')->limit(50)->wrap(),
                TextColumn::make('location')->label('Location')->searchable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'InProgress' => 'info',
                        'Completed' => 'success',
                        'Cancelled' => 'danger',
                    }),
                TextColumn::make('rating')->label('Rating')->sortable(),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'InProgress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
