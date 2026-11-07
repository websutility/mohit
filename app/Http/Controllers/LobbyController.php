<?php

namespace App\Http\Controllers;

use App\Services\Games\GameCatalog;
use Illuminate\View\View;

class LobbyController extends Controller
{
    public function index(GameCatalog $catalog): View
    {
        return view('lobby', [
            'games' => $catalog->all(),
        ]);
    }
}
