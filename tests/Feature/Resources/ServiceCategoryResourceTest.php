<?php

namespace Tests\Feature\Resources;

use App\Filament\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Tests\TestCase;

class ServiceCategoryResourceTest extends TestCase
{
    /** @test */
    public function it_uses_the_correct_model()
    {
        $this->assertEquals(ServiceCategory::class, ServiceCategoryResource::getModel());
    }

    /** @test */
    public function it_defines_expected_table_columns()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = ServiceCategoryResource::table($table);

        // Extract column names
        $columns = collect($table->getColumns())
            ->map(fn($column) => $column->getName())
            ->toArray();

        $expected = [
            'icon',
            'name',
            'status',
            'created_at',
        ];

        foreach ($expected as $columnName) {
            $this->assertContains(
                $columnName,
                $columns,
                "Column [{$columnName}] is not defined in the ServiceCategoryResource table."
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
        $form = ServiceCategoryResource::form($form);

        // Collect all component names from schema (flatten sections)
        $schema = collect($form->getComponents())
            ->flatMap(fn($component) => $component->getChildComponents())
            ->map(fn($component) => $component->getName())
            ->toArray();

        $expectedFields = ['name', 'description', 'icon'];

        foreach ($expectedFields as $fieldName) {
            $this->assertContains(
                $fieldName,
                $schema,
                "Form field [{$fieldName}] is not defined in the ServiceCategoryResource form schema."
            );
        }
    }

    /** @test */
    public function it_defines_expected_pages()
    {
        $pages = ServiceCategoryResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    /** @test */
    public function it_has_correct_navigation_settings()
    {
        $this->assertEquals('heroicon-o-cube', ServiceCategoryResource::getNavigationIcon());
        $this->assertEquals('Jobs', ServiceCategoryResource::getNavigationGroup());
        $this->assertEquals('Service Categories', ServiceCategoryResource::getNavigationLabel());
        $this->assertEquals('Service Category', ServiceCategoryResource::getModelLabel());
        $this->assertEquals('jobs/service-categories', ServiceCategoryResource::getSlug());
    }

    /** @test */
    public function it_defines_expected_filters()
    {
        // Mock the HasTable interface
        $mock = $this->createMock(HasTable::class);

        // Manually instantiate a Filament Table with the mock
        $table = new Table($mock);

        // Pass it to the resource
        $table = ServiceCategoryResource::table($table);

        // Extract filter names
        $filters = collect($table->getFilters())
            ->map(fn($filter) => $filter->getName())
            ->toArray();

        $expectedFilters = ['status'];

        foreach ($expectedFilters as $filterName) {
            $this->assertContains(
                $filterName,
                $filters,
                "Filter [{$filterName}] is not defined in the ServiceCategoryResource table."
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
        $table = ServiceCategoryResource::table($table);

        // Extract action names
        $actions = collect($table->getActions())
            ->map(fn($action) => $action->getName())
            ->toArray();

        $expectedActions = ['view'];

        foreach ($expectedActions as $actionName) {
            $this->assertContains(
                $actionName,
                $actions,
                "Action [{$actionName}] is not defined in the ServiceCategoryResource table."
            );
        }
    }
}
