<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = config('games.admin.username');
        $email = config('games.admin.email');
        $password = config('games.admin.password');

        if (! is_string($username) || $username === '' || ! is_string($email) || $email === '') {
            return;
        }

        if (! is_string($password) || strlen($password) < 8) {
            return;
        }

        $user = User::query()->firstOrNew(['username' => $username]);
        $user->fill([
            'name' => 'Floor admin',
            'email' => $email,
            'balance' => $user->exists ? $user->balance : 0,
        ]);
        $user->forceFill([
            'password' => $password,
            'is_admin' => true,
        ])->save();
    }
};
