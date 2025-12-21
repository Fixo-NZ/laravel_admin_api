<?php

namespace App\Filament\Resources\Service\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ServicesForm
{
    public static function configure(Form $form): Form
    {
        return $form->schema([
            Section::make('Services Details')
                ->description('Fill out the essential details for this service.')
                ->schema([
                TextInput::make('name')
                    ->label('Service Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(7)
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->createOptionModalHeading('Add New Services')
                    ->columnSpanFull(),
                ])
                ->columns(1)
                ->icon('heroicon-o-list-bullet'),
            ]);
        
    }
}
