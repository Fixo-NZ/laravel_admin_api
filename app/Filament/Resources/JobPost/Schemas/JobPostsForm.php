<?php

namespace App\Filament\Resources\JobPost\Schemas;

use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Service;

class JobPostsForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Section::make('Job Details')
                ->schema([
                    TextInput::make('number')
                        ->label('Job Number')
                        ->default(fn () => 'JOB-' . random_int(100000, 999999))
                        ->disabled()
                        ->dehydrated()
                        // ->required()
                        ->maxLength(32)
                        ->unique(\App\Models\HomeownerJobOffer::class, 'number', ignoreRecord: true),

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
                            'assigned' => 'Assigned',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),

                    FileUpload::make('photos')
                        ->label('Upload Job Photos')
                        ->multiple() // allow multiple uploads
                        ->directory('uploads/job_photos') // store in storage/app/public/uploads/job_photos
                        ->image() // validate as image
                        ->maxFiles(5) // max 8 images per job offer
                        ->preserveFilenames() // optional: keep original filenames
                        ->helperText('You can upload up to 8 images. Each will be saved in the job_offer_photos table.')
                        ->columnSpanFull(),
                    
                ])
                ->columns(2),

        ]);

    }
}
