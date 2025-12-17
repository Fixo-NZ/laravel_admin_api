<?php

namespace App\Filament\Resources\JobPost;

use App\Filament\Resources\JobPost\Pages;
use App\Filament\Resources\JobPost\Tables\JobPostsTable;
use App\Filament\Resources\JobPost\Schemas\JobPostsForm;
use App\Models\HomeownerJobOffer;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Group;
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
    return JobPostsForm::configure($form);
    }

    // ============================================================
    // TABLE DEFINITION
    // ============================================================    
    public static function table(Table $table): Table
    {
        return JobPostsTable::configure($table);
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
