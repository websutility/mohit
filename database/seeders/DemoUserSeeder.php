<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $players = [
            ['name' => 'pb113', 'username' => 'pb113', 'email' => 'pb113@amusement.test', 'balance' => 100000],
            ['name' => 'user_91', 'username' => 'user_91', 'email' => 'user91@amusement.test', 'balance' => 97973],
        ];

        foreach ($players as $player) {
            $user = User::query()->updateOrCreate(
                ['username' => $player['username']],
                [
                    'name' => $player['name'],
                    'email' => $player['email'],
                    'password' => 'password',
                    'balance' => $player['balance'],
                ],
            );

            WalletTransaction::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => 'signup',
                ],
                [
                    'amount' => $player['balance'],
                    'balance_after' => $player['balance'],
                    'note' => 'Opening points',
                ],
            );
        }
    }
}
