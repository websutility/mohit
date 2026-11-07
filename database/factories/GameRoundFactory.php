<?php

namespace Database\Factories;

use App\Models\GameRound;
use App\RoundStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameRound>
 */
class GameRoundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_key' => 'double-chance',
            'round_index' => fake()->unique()->numberBetween(1, 1000000),
            'status' => RoundStatus::Closed,
            'opens_at' => now()->subMinute(),
            'locks_at' => now(),
            'result' => ['andar' => '1', 'bahar' => '2', 'jodi' => '12'],
            'settled_at' => now(),
        ];
    }
}
