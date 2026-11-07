<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_admin_can_open_the_desk_and_credit_points(): void
    {
        $player = User::factory()->create(['balance' => 1000]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->get(route('admin.index'))->assertRedirect(route('login'));

        $this->actingAs($player)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($player)->post(route('admin.credit'), [
            'user_id' => $admin->id,
            'amount' => 500,
        ])->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Floor desk')
            ->assertSee($player->username);

        $this->actingAs($admin)->post(route('admin.credit'), [
            'user_id' => $player->id,
            'amount' => 2500,
            'note' => 'Opening float',
        ])->assertRedirect(route('admin.index'));

        $this->assertSame(3500, $player->fresh()->balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $player->id,
            'type' => 'admin',
            'amount' => 2500,
        ]);
    }

    public function test_admin_can_open_another_players_ticket(): void
    {
        $player = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $ticket = Ticket::factory()->create(['user_id' => $player->id]);

        $this->actingAs($admin)
            ->get(route('tickets.show', $ticket->code))
            ->assertOk()
            ->assertSee($ticket->code);

        $other = User::factory()->create();
        $this->actingAs($other)
            ->get(route('tickets.show', $ticket->code))
            ->assertNotFound();
    }

    public function test_seeder_creates_an_admin_from_configuration(): void
    {
        $password = Str::random(16);

        config([
            'games.admin.username' => 'flooradmin',
            'games.admin.email' => 'flooradmin@amusement.test',
            'games.admin.password' => $password,
        ]);

        $this->seed(AdminUserSeeder::class);

        $this->post(route('login.store'), [
            'username' => 'flooradmin',
            'password' => $password,
        ])->assertRedirect(route('admin.index'));

        $this->assertTrue(auth()->user()->is_admin);
    }

    public function test_seeder_skips_when_no_admin_password_is_configured(): void
    {
        config([
            'games.admin.username' => 'flooradmin',
            'games.admin.email' => 'flooradmin@amusement.test',
            'games.admin.password' => null,
        ]);

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseMissing('users', ['username' => 'flooradmin']);
    }
}
