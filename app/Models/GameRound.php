<?php

namespace App\Models;

use App\RoundStatus;
use Database\Factories\GameRoundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['game_key', 'round_index', 'status', 'opens_at', 'locks_at', 'result', 'settled_at'])]
class GameRound extends Model
{
    /** @use HasFactory<GameRoundFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'round_index' => 'integer',
            'status' => RoundStatus::class,
            'opens_at' => 'datetime',
            'locks_at' => 'datetime',
            'settled_at' => 'datetime',
            'result' => 'array',
        ];
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class, 'round_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'round_id');
    }
}
