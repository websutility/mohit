<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Games\GameCatalog;
use App\Services\Games\GameStateBuilder;
use Illuminate\Http\JsonResponse;

class GameStateController extends Controller
{
    public function show(string $game, GameCatalog $catalog, GameStateBuilder $builder): JsonResponse
    {
        if (! $catalog->has($game)) {
            abort(404);
        }

        return response()->json($builder->build(request()->user(), $game));
    }
}
