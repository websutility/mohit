<?php

namespace Database\Factories;

use App\Models\GameRound;
use App\Models\Ticket;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('????????')),
            'user_id' => User::factory(),
            'game_key' => 'double-chance',
            'round_id' => GameRound::factory(),
            'stake' => 10,
            'win' => 0,
            'status' => TicketStatus::Open,
        ];
    }
}
