<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** Homeowner Can Create Ticket */
    public function test_user_can_create_ticket()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'subject' => 'Login Issue',
            'description' => 'I cannot log in to my account.',
            'priority' => 'High',
            'category' => 'Account',
        ]);

        $response->assertStatus(302); // redirect after success

        $this->assertDatabaseHas('tickets', [
            'subject' => 'Login Issue',
            'user_id' => $user->id,
        ]);
    }

    /** User Can View Own Tickets */
    public function test_user_can_view_own_tickets()
    {
        $user = User::factory()->create();

        Ticket::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Water leak issue'
        ]);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('Water leak issue');
    }

    /** Admin Can View All Tickets */
    public function test_admin_can_view_all_tickets()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Ticket::factory()->create(['subject' => 'Broken sink']);
        Ticket::factory()->create(['subject' => 'Payment problem']);

        $response = $this->actingAs($admin)->get('/admin/tickets');

        $response->assertStatus(200);
        $response->assertSee('Broken sink');
        $response->assertSee('Payment problem');
    }

    /** Admin Can Update Ticket Status */
    public function test_admin_can_update_ticket_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create([
            'status' => 'Pending'
        ]);

        $response = $this->actingAs($admin)->put("/admin/tickets/{$ticket->id}", [
            'status' => 'In Progress'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'In Progress'
        ]);
    }

    /** Admin Can Delete Ticket (Soft Delete) */
    public function test_admin_can_delete_ticket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/tickets/{$ticket->id}");

        $response->assertStatus(302);

        $this->assertSoftDeleted('tickets', [
            'id' => $ticket->id
        ]);
    }

    /** Regular User Cannot Access Admin Tickets */
    public function test_user_cannot_access_admin_panel()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/tickets');

        $response->assertStatus(403);
    }

}
