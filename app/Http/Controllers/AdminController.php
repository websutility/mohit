<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'players' => User::query()->orderBy('username')->get(['id', 'username', 'balance', 'is_admin']),
            'tickets' => Ticket::query()->with('user:id,username')->latest('id')->limit(20)->get(),
        ]);
    }

    public function credit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000'],
            'note' => ['nullable', 'string', 'max:80'],
        ]);

        $added = DB::transaction(function () use ($data, $request): int {
            $player = User::query()->whereKey($data['user_id'])->lockForUpdate()->firstOrFail();
            $cap = (int) config('games.balance_cap');
            $added = min((int) $data['amount'], max(0, $cap - $player->balance));

            if ($added < 1) {
                return 0;
            }

            $player->balance += $added;
            $player->save();

            WalletTransaction::query()->create([
                'user_id' => $player->id,
                'type' => 'admin',
                'amount' => $added,
                'balance_after' => $player->balance,
                'note' => $data['note'] ?: 'Desk credit by '.$request->user()->username,
            ]);

            return $added;
        });

        if ($added < 1) {
            return redirect()->route('admin.index')->withErrors([
                'amount' => 'That player is already at the points cap.',
            ]);
        }

        return redirect()->route('admin.index');
    }
}
