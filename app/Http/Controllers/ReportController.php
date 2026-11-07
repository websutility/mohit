<?php

namespace App\Http\Controllers;

use App\Models\GameRound;
use App\Models\Ticket;
use App\RoundStatus;
use App\Services\Games\GameCatalog;
use App\Services\Games\RoundClock;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(RoundClock $clock, GameCatalog $catalog): View
    {
        $clock->syncAll();

        $tickets = Ticket::query()
            ->where('user_id', auth()->id())
            ->with('bets')
            ->latest('id')
            ->limit(40)
            ->get();

        $results = [];

        foreach ($catalog->all() as $key => $game) {
            $results[$key] = [
                'name' => $game['name'],
                'rounds' => GameRound::query()
                    ->where('game_key', $key)
                    ->where('status', RoundStatus::Closed)
                    ->orderByDesc('round_index')
                    ->limit(6)
                    ->get(),
            ];
        }

        return view('reports.index', [
            'tickets' => $tickets,
            'results' => $results,
        ]);
    }
}
