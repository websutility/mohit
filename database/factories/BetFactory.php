<?php

namespace Database\Factories;

use App\BetStatus;
use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bet>
 */
class BetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory(),
            'round_id' => GameRound::factory(),
            'game_key' => 'double-chance',
            'bet_type' => 'jodi',
            'selection' => '42',
            'amount' => 10,
            'odds' => '90.00',
            'payout' => 0,
            'status' => BetStatus::Pending,
        ];
    }
}
