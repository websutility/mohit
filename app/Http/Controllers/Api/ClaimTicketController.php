<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimTicketRequest;
use App\Models\Bet;
use App\Models\Ticket;
use App\Services\Games\RoundClock;
use App\TicketStatus;
use Illuminate\Http\JsonResponse;

class ClaimTicketController extends Controller
{
    public function __invoke(ClaimTicketRequest $request, RoundClock $clock): JsonResponse
    {
        $ticket = Ticket::query()
            ->where('code', strtoupper($request->validated('code')))
            ->where('user_id', $request->user()->id)
            ->first();

        if ($ticket === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Ticket not found.',
            ], 404);
        }

        $clock->sync($ticket->game_key);
        $ticket->refresh()->load(['bets', 'round']);

        if ($ticket->status === TicketStatus::Settled && $ticket->claimed_at === null) {
            $ticket->claimed_at = now();
            $ticket->save();
        }

        return response()->json([
            'ok' => true,
            'ticket' => [
                'code' => $ticket->code,
                'game' => $ticket->game_key,
                'stake' => $ticket->stake,
                'win' => $ticket->win,
                'status' => $ticket->status->value,
                'draw_time' => $ticket->round?->locks_at?->timezone(config('app.timezone'))->format('d/m/Y h:i A'),
                'bets' => $ticket->bets->map(fn (Bet $bet): array => [
                    'type' => $bet->bet_type,
                    'selection' => $bet->selection,
                    'amount' => $bet->amount,
                    'odds' => (string) $bet->odds,
                    'payout' => $bet->payout,
                    'status' => $bet->status->value,
                ])->all(),
            ],
        ]);
    }
}
