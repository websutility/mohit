@extends('layouts.site')

@section('content')
    <form class="auth-card" method="post" action="{{ route('register.store') }}">
        @csrf
        <h1>New player</h1>
        <p>You start with {{ number_format(config('games.signup_bonus')) }} amusement points.</p>
        <label>Name <input name="name" value="{{ old('name') }}" required></label>
        <label>Username <input name="username" value="{{ old('username') }}" required></label>
        <label>Email <input name="email" type="email" value="{{ old('email') }}" required></label>
        <label>Password <input name="password" type="password" required></label>
        <label>Confirm <input name="password_confirmation" type="password" required></label>
        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif
        <button class="btn gold lg" type="submit">Create and enter</button>
        <a href="{{ route('login') }}">Already have a player?</a>
    </form>
@endsection
