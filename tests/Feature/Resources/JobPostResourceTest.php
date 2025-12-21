<?php

namespace Tests\Feature\Resources;

use App\Filament\Resources\JobPostResource;
use App\Models\HomeownerJobOffer;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Tests\TestCase;

class JobPostResourceTest extends TestCase
{
    /** @test */
    public function it_uses_the_correct_model()
    {
        $this->assertEquals(HomeownerJobOffer::class, JobPostResource::getModel());
    }

    /** @test */
    public function it_defines_expected_table_columns()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = JobPostResource::table($table);

        // Extract column names
        $columns = collect($table->getColumns())
            ->map(fn($column) => $column->getName())
            ->toArray();

        $expected = [
            'id',
            'title',
            'description',
            'homeowner.full_name',
            'category.name',
            'job_type',
            'job_size',
            'preferred_date',
            'address',
            'services_count',
            'status',
            'created_at',
        ];

        foreach ($expected as $columnName) {
            $this->assertContains(
                $columnName,
                $columns,
                "Column [{$columnName}] is not defined in the JobPostResource table."
            );
        }
    }

    /** @test */
    public function it_defines_expected_form_fields()
    {
        // Mock HasForms interface (required for Form instantiation)
        $mock = $this->createMock(HasForms::class);

        // Create a form with the mocked context
        $form = new Form($mock);

        // Pass it to the resource
        $form = JobPostResource::form($form);

        // Collect all component names from schema (flatten sections)
        $schema = collect($form->getComponents())
            ->flatMap(fn($component) => $component->getChildComponents())
            ->map(fn($component) => $component->getName())
            ->toArray();

        $expectedFields = [
            'homeowner_id',
            'service_category_id',
            'title',
            'description',
            'job_type',
            'frequency',
            'start_date',
            'end_date',
            'preferred_date',
            'job_size',
            'status',
            'address',
            'latitude',
            'longitude',
            'services',
        ];

        foreach ($expectedFields as $fieldName) {
            $this->assertContains(
                $fieldName,
                $schema,
                "Form field [{$fieldName}] is not defined in the JobPostResource form schema."
            );
        }
    }

    /** @test */
    public function it_defines_expected_pages()
    {
        $pages = JobPostResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    /** @test */
    public function it_has_correct_navigation_settings()
    {
        $this->assertEquals('heroicon-o-wrench-screwdriver', JobPostResource::getNavigationIcon());
        $this->assertEquals('Jobs', JobPostResource::getNavigationGroup());
        $this->assertEquals('Job Postings', JobPostResource::getNavigationLabel());
        $this->assertEquals('job-postings', JobPostResource::getSlug());
    }

    /** @test */
    public function it_defines_expected_filters()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = JobPostResource::table($table);

        // Extract filter names
        $filters = collect($table->getFilters())
            ->map(fn($filter) => $filter->getName())
            ->toArray();

        $expectedFilters = ['status', 'job_type', 'job_size', 'service_category_id'];

        foreach ($expectedFilters as $filterName) {
            $this->assertContains(
                $filterName,
                $filters,
                "Filter [{$filterName}] is not defined in the JobPostResource table."
            );
        }
    }

    /** @test */
    public function it_defines_expected_actions()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = JobPostResource::table($table);

        // Extract action names
        $actions = collect($table->getActions())
            ->map(fn($action) => $action->getName())
            ->toArray();

        $expectedActions = ['edit', 'delete'];

        foreach ($expectedActions as $actionName) {
            $this->assertContains(
                $actionName,
                $actions,
                "Action [{$actionName}] is not defined in the JobPostResource table."
            );
        }
    }

    /** @test */
    public function it_defines_expected_bulk_actions()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = JobPostResource::table($table);

        // Extract bulk action names (flatten from groups)
        $bulkActions = collect($table->getBulkActions())
            ->flatMap(fn($group) => $group->getActions())
            ->map(fn($action) => $action->getName())
            ->toArray();

        $expectedBulkActions = ['delete'];

        foreach ($expectedBulkActions as $bulkActionName) {
            $this->assertContains(
                $bulkActionName,
                $bulkActions,
                "Bulk action [{$bulkActionName}] is not defined in the JobPostResource table."
            );
        }
    }

    /** @test */
    public function it_cannot_create_new_records()
    {
        $this->assertFalse(JobPostResource::canCreate());
    }
}
