<?php

namespace App\Services\Games;

use App\BetStatus;
use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WalletTransaction;
use App\RoundStatus;
use App\TicketStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoundClock
{
    public function __construct(
        private GameCatalog $catalog,
        private ResultFactory $results,
        private BetRules $rules,
    ) {}

    public function cycle(): int
    {
        return (int) config('games.bet_seconds') + (int) config('games.spin_seconds');
    }

    public function currentIndex(?Carbon $moment = null): int
    {
        $moment ??= now();

        return intdiv($moment->getTimestamp(), $this->cycle());
    }

    public function opensAt(int $index): Carbon
    {
        return Carbon::createFromTimestamp($index * $this->cycle(), config('app.timezone'));
    }

    public function locksAt(int $index): Carbon
    {
        return Carbon::createFromTimestamp($index * $this->cycle() + (int) config('games.bet_seconds'), config('app.timezone'));
    }

    public function phase(int $index, ?Carbon $moment = null): RoundStatus
    {
        $moment ??= now();

        if ($moment->greaterThanOrEqualTo($this->locksAt($index))) {
            return RoundStatus::Spinning;
        }

        return RoundStatus::Open;
    }

    public function secondsRemaining(GameRound $round): int
    {
        $now = now()->getTimestamp();

        if ($round->status === RoundStatus::Open) {
            return max(0, $round->locks_at->getTimestamp() - $now);
        }

        if ($round->status === RoundStatus::Spinning) {
            return max(0, $this->opensAt($round->round_index + 1)->getTimestamp() - $now);
        }

        return 0;
    }

    public function sync(string $gameKey): GameRound
    {
        return DB::transaction(function () use ($gameKey): GameRound {
            $index = $this->currentIndex();
            $this->closeStale($gameKey, $index);
            $this->backfill($gameKey, $index);

            return $this->ensureCurrent($gameKey, $index);
        });
    }

    public function syncAll(): void
    {
        foreach ($this->catalog->keys() as $key) {
            $this->sync($key);
        }
    }

    private function closeStale(string $gameKey, int $index): void
    {
        $stale = GameRound::query()
            ->where('game_key', $gameKey)
            ->where('round_index', '<', $index)
            ->whereNull('settled_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($stale as $round) {
            $this->finish($round);
        }
    }

    private function finish(GameRound $round): void
    {
        if ($round->settled_at !== null) {
            return;
        }

        if (! is_array($round->result)) {
            $round->result = $this->results->make($round->game_key);
        }

        $round->status = RoundStatus::Closed;
        $this->settle($round);
        $round->settled_at = now();
        $round->save();
    }

    private function settle(GameRound $round): void
    {
        $bets = Bet::query()
            ->where('round_id', $round->id)
            ->where('status', BetStatus::Pending)
            ->get();

        if ($bets->isEmpty()) {
            return;
        }

        $userIds = $bets->pluck('user_id')->unique()->sort()->values();
        $users = User::query()
            ->whereIn('id', $userIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $ticketWins = [];

        foreach ($bets as $bet) {
            $won = $this->rules->wins($round->game_key, $bet->bet_type, $bet->selection, $round->result ?? []);

            if ($won) {
                $payout = $this->rules->payout($bet->amount, (string) $bet->odds);
                $bet->status = BetStatus::Won;
                $bet->payout = $payout;
                $ticketWins[$bet->ticket_id] = ($ticketWins[$bet->ticket_id] ?? 0) + $payout;
            } else {
                $bet->status = BetStatus::Lost;
                $bet->payout = 0;
            }

            $bet->save();
        }

        $tickets = Ticket::query()
            ->whereIn('id', $bets->pluck('ticket_id')->unique()->values())
            ->lockForUpdate()
            ->get();

        foreach ($tickets as $ticket) {
            $win = $ticketWins[$ticket->id] ?? 0;
            $ticket->win = $win;
            $ticket->status = TicketStatus::Settled;
            $ticket->save();

            if ($win <= 0) {
                continue;
            }

            /** @var User $user */
            $user = $users->get($ticket->user_id);
            $user->balance += $win;
            $user->save();

            WalletTransaction::query()->create([
                'user_id' => $user->id,
                'type' => 'win',
                'amount' => $win,
                'balance_after' => $user->balance,
                'ticket_id' => $ticket->id,
                'note' => 'Win on '.$round->game_key,
            ]);
        }
    }

    private function backfill(string $gameKey, int $index): void
    {
        $keep = (int) config('games.history_size');
        $oldest = max(0, $index - $keep);

        for ($i = $index - 1; $i >= $oldest; $i--) {
            DB::table('game_rounds')->insertOrIgnore([
                'game_key' => $gameKey,
                'round_index' => $i,
                'status' => RoundStatus::Closed->value,
                'opens_at' => $this->opensAt($i),
                'locks_at' => $this->locksAt($i),
                'result' => json_encode($this->results->make($gameKey)),
                'settled_at' => $this->locksAt($i),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function ensureCurrent(string $gameKey, int $index): GameRound
    {
        $phase = $this->phase($index);

        DB::table('game_rounds')->insertOrIgnore([
            'game_key' => $gameKey,
            'round_index' => $index,
            'status' => $phase->value,
            'opens_at' => $this->opensAt($index),
            'locks_at' => $this->locksAt($index),
            'result' => $phase === RoundStatus::Spinning ? json_encode($this->results->make($gameKey)) : null,
            'settled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $round = GameRound::query()
            ->where('game_key', $gameKey)
            ->where('round_index', $index)
            ->lockForUpdate()
            ->firstOrFail();

        if ($phase === RoundStatus::Spinning && ! is_array($round->result)) {
            $round->result = $this->results->make($gameKey);
        }

        if ($round->status !== $phase) {
            $round->status = $phase;
        }

        if ($round->isDirty()) {
            $round->save();
        }

        return $round;
    }
}
