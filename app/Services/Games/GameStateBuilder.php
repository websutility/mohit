<?php

namespace App\Services\Games;

use App\BetStatus;
use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Ticket;
use App\Models\User;
use App\RoundStatus;

class GameStateBuilder
{
    public function __construct(
        private GameCatalog $catalog,
        private RoundClock $clock,
        private BetRules $rules,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, string $gameKey): array
    {
        $round = $this->clock->sync($gameKey);
        $game = $this->catalog->get($gameKey);

        $history = GameRound::query()
            ->where('game_key', $gameKey)
            ->where('status', RoundStatus::Closed)
            ->orderByDesc('round_index')
            ->limit((int) config('games.history_size'))
            ->get();

        $myBets = Bet::query()
            ->where('user_id', $user->id)
            ->where('round_id', $round->id)
            ->get();

        $lastTicket = Ticket::query()
            ->where('user_id', $user->id)
            ->where('game_key', $gameKey)
            ->latest('id')
            ->with('bets')
            ->first();

        $previous = $history->first();
        $previousWin = 0;

        if ($previous !== null) {
            $previousWin = (int) Bet::query()
                ->where('user_id', $user->id)
                ->where('round_id', $previous->id)
                ->sum('payout');
        }

        $sinceOpen = now()->getTimestamp() - $round->opens_at->getTimestamp();

        return [
            'ok' => true,
            'balance' => $user->fresh()->balance,
            'username' => $user->username,
            'game' => [
                'key' => $gameKey,
                'name' => $game['name'],
            ],
            'server_now' => now()->toIso8601String(),
            'round' => [
                'id' => $round->id,
                'index' => $round->round_index,
                'status' => $round->status->value,
                'seconds_left' => $this->clock->secondsRemaining($round),
                'draw_time' => $round->locks_at->timezone(config('app.timezone'))->format('h:i A'),
                'result' => $round->result,
            ],
            'history' => $history->map(fn (GameRound $past): array => [
                'draw_time' => $past->locks_at->timezone(config('app.timezone'))->format('h:i A'),
                'draw_date' => $past->locks_at->timezone(config('app.timezone'))->format('d/m/Y'),
                'result' => $past->result,
            ])->all(),
            'my_bets' => $myBets->map(fn (Bet $bet): array => [
                'type' => $bet->bet_type,
                'selection' => $bet->selection,
                'amount' => $bet->amount,
                'odds' => (string) $bet->odds,
                'status' => $bet->status->value,
                'payout' => $this->assessed($bet, $round),
            ])->all(),
            'round_win' => $myBets->sum(fn (Bet $bet): int => $this->assessed($bet, $round)),
            'previous_win' => $previousWin,
            'highlight_previous' => $round->status === RoundStatus::Open && $sinceOpen >= 0 && $sinceOpen < 8,
            'last_slip' => $lastTicket === null ? [] : $lastTicket->bets->map(fn (Bet $bet): array => [
                'type' => $bet->bet_type,
                'selection' => $bet->selection,
                'amount' => $bet->amount,
            ])->all(),
            'last_ticket' => $lastTicket?->code,
        ];
    }

    private function assessed(Bet $bet, GameRound $round): int
    {
        if ($bet->status === BetStatus::Won) {
            return $bet->payout;
        }

        if ($bet->status === BetStatus::Lost) {
            return 0;
        }

        if (! is_array($round->result)) {
            return 0;
        }

        if (! $this->rules->wins($round->game_key, $bet->bet_type, $bet->selection, $round->result)) {
            return 0;
        }

        return $this->rules->payout($bet->amount, (string) $bet->odds);
    }
}
