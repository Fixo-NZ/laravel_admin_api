<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobPostResource\Pages;
use App\Models\HomeownerJobOffer;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

 class JobPostResource extends Resource
{
    // ============================================================
    // PAGE CONFIGURATION
    // ============================================================
    
    // The Eloquent model this resource manages
    protected static ?string $model = HomeownerJobOffer::class;

    // Icon to display in the sidebar (use Heroicons names)
    protected static ?string $navigationIcon = null;
    
    // Label for the navigation item 
    protected static ?string $navigationLabel = 'Job Postings';
    
    // Navigation group in the sidebar
    protected static ?string $navigationGroup = 'Job Oversight';

    // Model Label
    protected static ?string $modelLabel = 'Job Post';
    
    // Slug for the resource URLs
    protected static ?string $slug = 'job-postings';
    
    // Default sort to display the newest jobs first
    protected static ?string $defaultSort = 'created_at';
    protected static ?string $defaultSortDirection = 'desc';

    // Auto-refresh interval (in seconds)
    protected static int $pollingInterval = 5;


    // ============================================================
    // FORM DEFINITION
    // ============================================================
    public static function form(Form $form): Form
    {
    return $form
        ->schema([
            Section::make('Job Details')
                ->schema([
                    Select::make('homeowner_id')
                        ->relationship('homeowner', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                        ->label('Homeowner')
                        ->disabled()
                        ->dehydrated(false),

                    Select::make('service_category_id')
                        ->relationship('category', 'name')
                        ->label('Service Category')
                        ->required()
                        ->searchable()
                        ->reactive()
                        ->preload(),

                    TextInput::make('title')
                        ->label('Job Title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Enter job title...'),

                    Textarea::make('description')
                        ->label('Description')
                        ->maxLength(300)
                        ->rows(3)
                        ->placeholder('Briefly describe the job requirements...'),

                    Select::make('job_type')
                        ->label('Job Type')
                        ->options([
                            'standard' => 'Standard',
                            'urgent' => 'Urgent',
                            'recurrent' => 'Recurrent',
                        ])
                        ->required()
                        ->reactive(),

                    // Conditional recurrence fields
                    Select::make('frequency')
                        ->label('Recurrent Frequency')
                        ->options([
                            'daily' => 'Daily',
                            'weekly' => 'Weekly',
                            'monthly' => 'Monthly',
                            'quarterly' => 'Quarterly',
                            'yearly' => 'Yearly',
                            'custom' => 'Custom',
                        ])
                        ->visible(fn(callable $get) => $get('job_type') === 'recurrent')
                        ->nullable(),

                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->visible(fn(callable $get) => $get('job_type') === 'recurrent')
                        ->nullable(),

                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->visible(fn(callable $get) => $get('job_type') === 'recurrent')
                        ->afterOrEqual('start_date')
                        ->nullable(),

                    DatePicker::make('preferred_date')
                        ->label('Preferred Date')
                        ->nullable(),

                    Select::make('job_size')
                        ->label('Job Size')
                        ->options([
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                        ])
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'open' => 'Open',
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                ])
                ->columns(2),

            Section::make('Location Details')
                ->schema([
                    TextInput::make('address')
                        ->label('Address')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Enter full address...'),

                    TextInput::make('latitude')
                        ->numeric()
                        ->label('Latitude')
                        ->nullable(),

                    TextInput::make('longitude')
                        ->numeric()
                        ->label('Longitude')
                        ->nullable(),
                ])
                ->columns(2),

            Section::make('Services')
                ->schema([
                    Select::make('services')
                        ->label('Related Services')
                        ->multiple()
                        ->required()
                        ->relationship('services', 'name')
                        ->preload()
                        ->searchable()
                        ->options(function (callable $get) {
                            $categoryId = $get('service_category_id');
                            if (!$categoryId) {
                                return [];
                            }

                            return Service::where('category_id', $categoryId)
                                ->pluck('name', 'id');
                        })
                        ->reactive(),
                ]),
        ]);
    }

    // ============================================================
    // TABLE DEFINITION
    // ============================================================    
    public static function table(Table $table): Table
    {
        return $table
        
            // ============================================================
            // Columns
            // ============================================================    
            ->columns([
                // Job ID
                TextColumn::make('id')
                    ->label('Job ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Job Title (Searchable)
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Homeowner ID/Name
                TextColumn::make('homeowner.full_name') 
                    ->label('Homeowner')
                    ->sortable()
                    ->toggleable(),

                // Service Category
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                // Job Type
                TextColumn::make('job_type')
                    ->label('Job Type')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'standard',
                        'danger' => fn($state) => strtolower($state) === 'urgent',
                        'info'    => fn($state) => strtolower($state) === 'recurrent',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->extraAttributes([
                        'class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs',
                    ])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Job Size
                TextColumn::make('job_size')
                    ->label('Job Size')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'large',
                        'warning' => fn($state) => strtolower($state) === 'medium',
                        'info'    => fn($state) => strtolower($state) === 'small',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->extraAttributes([
                        'class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs',
                    ])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('preferred_date')
                    ->label('Preferred Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('address')
                    ->label('Address')
                    ->toggleable(isToggledHiddenByDefault: true),  
                
                TextColumn::make('services_count')
                    ->label('Services')
                    ->counts('services')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Status (Using TextColumn with badge() for visual tracking, replacing deprecated BadgeColumn)
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => strtolower($state) === 'open',
                        'danger'  => fn($state) => in_array(strtolower($state), ['cancelled', 'expired']),
                        'warning' => fn($state) => strtolower($state) === 'pending',
                        'primary' => fn($state) => strtolower($state) === 'completed',
                        'info'    => fn($state) => strtolower($state) === 'in_progress',
                    ])
                    ->formatStateUsing(function ($state) {
                        return Str::of($state)->replace('_', ' ')->title();
                    })
                    ->extraAttributes([
                        'class' => 'px-3 py-1 rounded-full text-white font-semibold text-xs',
                    ])
                    ->sortable(),

                // Created At
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ============================================================
            // Filters
            // ============================================================
            ->filters([
                // Filter for Status
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'in_progress' => 'In Progress',
                    ]),

                // Filter for Job Type
                SelectFilter::make('job_type')
                    ->options([
                        'standard' => 'Standard',
                        'urgent' => 'Urgent',
                        'recurrent' => 'Recurrent',
                    ]),
                
                // Filter for Job Size
                SelectFilter::make('job_size')
                    ->options([
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                    ]),

                // Filter for Service Category
                SelectFilter::make('service_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ])

            // ============================================================
            // Actions
            // ============================================================
            ->actions([
                // Actions (Mirrors the 'Actions' column from the HTML mock-up)
                EditAction::make(),
                DeleteAction::make(),
            ])

            // ============================================================
            // Bulk Actions
            // ============================================================
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobPosts::route('/'),
            'create' => Pages\CreateJobPost::route('/create'),
            'edit' => Pages\EditJobPost::route('/{record}/edit'),
        ];
    }
    
    // Remove Create Action and Button
    public static function canCreate(): bool
    {
        return false;
    }
}
