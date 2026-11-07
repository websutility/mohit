<?php

namespace App\Services\Games;

use App\BetStatus;
use App\Exceptions\InvalidBetException;
use App\Models\Bet;
use App\Models\GameRound;
use App\Models\Ticket;
use App\Models\User;
use App\Models\WalletTransaction;
use App\RoundStatus;
use App\TicketStatus;
use Illuminate\Support\Facades\DB;

class BetPlacement
{
    public function __construct(
        private GameCatalog $catalog,
        private RoundClock $clock,
        private BetRules $rules,
    ) {}

    /**
     * @param  list<array{type: string, selection: string, amount: int}>  $bets
     */
    public function place(User $user, string $gameKey, array $bets): Ticket
    {
        if (! $this->catalog->has($gameKey)) {
            throw new InvalidBetException('Unknown game.');
        }

        $this->clock->sync($gameKey);

        return DB::transaction(function () use ($user, $gameKey, $bets): Ticket {
            $round = GameRound::query()
                ->where('game_key', $gameKey)
                ->where('round_index', $this->clock->currentIndex())
                ->lockForUpdate()
                ->first();

            if ($round === null || $round->status !== RoundStatus::Open || now()->greaterThanOrEqualTo($round->locks_at)) {
                throw new InvalidBetException('Betting is closed for this draw.');
            }

            $merged = $this->merge($gameKey, $bets);
            $total = array_sum(array_column($merged, 'amount'));

            if ($total > (int) config('games.max_ticket')) {
                throw new InvalidBetException('Ticket is over the maximum stake.');
            }

            $account = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($account->balance < $total) {
                throw new InvalidBetException('Not enough points.');
            }

            $account->balance -= $total;
            $account->save();

            $ticket = Ticket::query()->create([
                'code' => $this->makeCode(),
                'user_id' => $account->id,
                'game_key' => $gameKey,
                'round_id' => $round->id,
                'stake' => $total,
                'win' => 0,
                'status' => TicketStatus::Open,
            ]);

            foreach ($merged as $bet) {
                Bet::query()->create([
                    'user_id' => $account->id,
                    'ticket_id' => $ticket->id,
                    'round_id' => $round->id,
                    'game_key' => $gameKey,
                    'bet_type' => $bet['type'],
                    'selection' => $bet['selection'],
                    'amount' => $bet['amount'],
                    'odds' => $bet['odds'],
                    'payout' => 0,
                    'status' => BetStatus::Pending,
                ]);
            }

            WalletTransaction::query()->create([
                'user_id' => $account->id,
                'type' => 'bet',
                'amount' => -$total,
                'balance_after' => $account->balance,
                'ticket_id' => $ticket->id,
                'note' => 'Bet on '.$gameKey,
            ]);

            return $ticket->load('bets');
        });
    }

    /**
     * @param  list<array{type: string, selection: string, amount: int}>  $bets
     * @return list<array{type: string, selection: string, amount: int, odds: string}>
     */
    private function merge(string $gameKey, array $bets): array
    {
        $merged = [];

        foreach ($bets as $bet) {
            $type = (string) $bet['type'];
            $amount = (int) $bet['amount'];

            if ($amount < 1 || $amount > (int) config('games.max_spot')) {
                throw new InvalidBetException('Chip amount is out of range.');
            }

            $selection = $this->rules->normalize($gameKey, $type, (string) $bet['selection']);
            $odds = $this->rules->quote($gameKey, $type, $selection);
            $key = $type.'|'.$selection;

            if (! isset($merged[$key])) {
                $merged[$key] = [
                    'type' => $type,
                    'selection' => $selection,
                    'amount' => 0,
                    'odds' => $odds,
                ];
            }

            $merged[$key]['amount'] += $amount;

            if ($merged[$key]['amount'] > (int) config('games.max_spot')) {
                throw new InvalidBetException('Maximum stake on one spot is '.config('games.max_spot').'.');
            }
        }

        return array_values($merged);
    }

    private function makeCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;

        do {
            $code = '';

            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, $max)];
            }
        } while (Ticket::query()->where('code', $code)->exists());

        return $code;
    }
}
