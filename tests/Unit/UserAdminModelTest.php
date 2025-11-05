<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAdminModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user_with_fillable_attributes()
    {
        $user = User::factory()->create([
            'first_name' => 'Helena',
            'last_name' => 'Mahinay',
            'middle_name' => 'R.',
            'email' => 'helena@example.com',
            'role' => 'admin',
            'status' => 'active',
            'phone' => '09123456789',
            'location' => 'Cebu City',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'helena@example.com',
            'first_name' => 'Helena',
            'last_name' => 'Mahinay',
        ]);
    }

    /** @test */
    public function it_combines_first_and_last_name_in_name_accessor()
    {
        $user = User::factory()->make([
            'first_name' => 'Helena',
            'last_name' => 'Mahinay',
        ]);

        $this->assertEquals('Helena Mahinay', $user->name);
    }

    /** @test */
    public function it_returns_filament_name_correctly()
    {
        $user = User::factory()->make([
            'first_name' => 'Helena',
            'last_name' => 'Mahinay',
        ]);

        $this->assertEquals('Helena Mahinay', $user->getFilamentName());
    }

    /** @test */
    public function it_hashes_the_password_when_creating_user()
    {
        $user = User::factory()->create([
            'password' => 'plaintext123',
        ]);

        $this->assertNotEquals('plaintext123', $user->password);
        $this->assertTrue(password_verify('plaintext123', $user->password));
    }

    /** @test */
    public function it_can_access_filament_panel()
    {
        $user = User::factory()->create();
        $mockPanel = $this->createMock(Panel::class);

        $this->assertTrue($user->canAccessPanel($mockPanel));
    }
}
