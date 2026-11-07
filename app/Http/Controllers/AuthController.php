<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Those credentials do not match our records.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $home = $user->is_admin ? route('admin.index') : route('lobby');

        return redirect()->intended($home);
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $bonus = (int) config('games.signup_bonus');

        $user = User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'balance' => $bonus,
        ]);

        WalletTransaction::query()->create([
            'user_id' => $user->id,
            'type' => 'signup',
            'amount' => $bonus,
            'balance_after' => $bonus,
            'note' => 'Starting points',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('lobby');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('lobby');
    }
}
