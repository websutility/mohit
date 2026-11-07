<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefillController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return DB::transaction(function () use ($request): JsonResponse {
            $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $cap = (int) config('games.balance_cap');
            $gift = (int) config('games.refill_amount');

            if ($user->balance >= $cap) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Balance is already full.',
                ], 422);
            }

            $added = min($gift, $cap - $user->balance);
            $user->balance += $added;
            $user->save();

            WalletTransaction::query()->create([
                'user_id' => $user->id,
                'type' => 'refill',
                'amount' => $added,
                'balance_after' => $user->balance,
                'note' => 'Amusement refill',
            ]);

            return response()->json([
                'ok' => true,
                'balance' => $user->balance,
                'added' => $added,
            ]);
        });
    }
}
