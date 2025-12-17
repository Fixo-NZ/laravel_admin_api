<?php

namespace App\Filament\Resources\ServiceCategory\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ServiceCategoriesForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Section::make('Category Details')
                ->description('Fill out the essential details for this service category.')
                ->schema([
                    TextInput::make('name')
                        ->label('Category Namea')
                        ->placeholder('e.g., Plumbing, Electrical, Carpentry')
                        ->required()
                        ->maxLength(255)
                        ->helperText('This will be visible to homeowners when they choose a service.')

                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Briefly describe what this service category includes...')
                        ->rows(3)
                        ->maxLength(500)
                        ->nullable()
                        ->helperText('A short explanation helps users understand when to select this category.')

                        ->columnSpanFull(),

                    TextInput::make('icon')
                        ->label('Icon Name')
                        ->placeholder('Enter the icon filename (without .svg)')
                        ->suffixIcon('heroicon-o-photo')
                        ->maxLength(255)
                        ->nullable()
                        ->helperText('Icons must be stored in: storage/app/public/icons/')
                        ->columnSpanFull(),
                    
                ])
                ->columns(1)
                ->icon('heroicon-o-list-bullet'),
        ]);
    }
}
