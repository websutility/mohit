<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\Games\GameCatalog;
use App\Services\Games\RoundClock;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function show(string $code, RoundClock $clock, GameCatalog $catalog): View
    {
        $ticket = Ticket::query()
            ->where('code', strtoupper($code))
            ->where('user_id', auth()->id())
            ->with(['bets', 'round'])
            ->firstOrFail();

        $clock->sync($ticket->game_key);
        $ticket->refresh()->load(['bets', 'round']);

        return view('tickets.print', [
            'ticket' => $ticket,
            'gameName' => $catalog->has($ticket->game_key) ? $catalog->get($ticket->game_key)['name'] : $ticket->game_key,
        ]);
    }
}
