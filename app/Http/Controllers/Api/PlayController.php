<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidBetException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceBetRequest;
use App\Models\Bet;
use App\Services\Games\BetPlacement;
use Illuminate\Http\JsonResponse;

class PlayController extends Controller
{
    public function __invoke(PlaceBetRequest $request, string $game, BetPlacement $placement): JsonResponse
    {
        try {
            $ticket = $placement->place($request->user(), $game, $request->validated('bets'));
        } catch (InvalidBetException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'balance' => $request->user()->fresh()->balance,
            'ticket' => [
                'code' => $ticket->code,
                'stake' => $ticket->stake,
                'bets' => $ticket->bets->map(fn (Bet $bet): array => [
                    'type' => $bet->bet_type,
                    'selection' => $bet->selection,
                    'amount' => $bet->amount,
                    'odds' => (string) $bet->odds,
                ])->all(),
            ],
        ]);
    }
}
