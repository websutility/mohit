<?php

namespace App\Models;

use App\BetStatus;
use Database\Factories\BetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'ticket_id', 'round_id', 'game_key', 'bet_type', 'selection', 'amount', 'odds', 'payout', 'status'])]
class Bet extends Model
{
    /** @use HasFactory<BetFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'odds' => 'decimal:2',
            'payout' => 'integer',
            'status' => BetStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'round_id');
    }
}
