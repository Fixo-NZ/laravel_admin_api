<?php

namespace Tests\Feature\Resources;

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
{
    /** @test */
    public function it_uses_the_correct_model()
    {
        $this->assertEquals(Service::class, ServiceResource::getModel());
    }

    /** @test */
    public function it_defines_expected_table_columns()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = ServiceResource::table($table);

        // Extract column names
        $columns = collect($table->getColumns())
            ->map(fn($column) => $column->getName())
            ->toArray();

        $expected = [
            'id',
            'name',
            'description',
            'category.name',
            'status',
            'created_at',
        ];

        foreach ($expected as $columnName) {
            $this->assertContains(
                $columnName,
                $columns,
                "Column [{$columnName}] is not defined in the ServiceResource table."
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
        $form = ServiceResource::form($form);

        // Collect all component names from schema
        $schema = collect($form->getComponents())
            ->map(fn($component) => $component->getName())
            ->toArray();

        $expectedFields = ['name', 'description', 'category_id'];

        foreach ($expectedFields as $fieldName) {
            $this->assertContains(
                $fieldName,
                $schema,
                "Form field [{$fieldName}] is not defined in the ServiceResource form schema."
            );
        }
    }

    /** @test */
    public function it_defines_expected_pages()
    {
        $pages = ServiceResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    /** @test */
    public function it_has_correct_navigation_settings()
    {
        $this->assertEquals('heroicon-o-briefcase', ServiceResource::getNavigationIcon());
        $this->assertEquals('Jobs', ServiceResource::getNavigationGroup());
        $this->assertEquals('Services', ServiceResource::getNavigationLabel());
        $this->assertEquals('Services', ServiceResource::getModelLabel());
        $this->assertEquals('jobs/services', ServiceResource::getSlug());
    }

    /** @test */
    public function it_defines_expected_filters()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = ServiceResource::table($table);

        // Extract filter names
        $filters = collect($table->getFilters())
            ->map(fn($filter) => $filter->getName())
            ->toArray();

        $expectedFilters = ['status', 'category'];

        foreach ($expectedFilters as $filterName) {
            $this->assertContains(
                $filterName,
                $filters,
                "Filter [{$filterName}] is not defined in the ServiceResource table."
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
        $table = ServiceResource::table($table);

        // Extract action names
        $actions = collect($table->getActions())
            ->map(fn($action) => $action->getName())
            ->toArray();

        $expectedActions = ['viewDetails'];

        foreach ($expectedActions as $actionName) {
            $this->assertContains(
                $actionName,
                $actions,
                "Action [{$actionName}] is not defined in the ServiceResource table."
            );
        }
    }
}
