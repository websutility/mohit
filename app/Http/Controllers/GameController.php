<?php

namespace App\Http\Controllers;

use App\Services\Games\GameCatalog;
use Illuminate\View\View;

class GameController extends Controller
{
    public function show(string $game, GameCatalog $catalog): View
    {
        if (! $catalog->has($game)) {
            abort(404);
        }

        return view('games.show', [
            'gameKey' => $game,
            'game' => $catalog->get($game),
            'games' => $catalog->all(),
            'horses' => $catalog->horses(),
        ]);
    }
}
