@extends('layouts.site')

@section('content')
    <form class="auth-card" method="post" action="{{ route('login.store') }}">
        @csrf
        <h1>Welcome back</h1>
        <p>Points stay on this terminal. Nothing here is cash.</p>
        <label>
            Username or email
            <input id="username" name="username" value="{{ old('username') }}" required autofocus>
        </label>
        <label>
            Password
            <input id="password" name="password" type="password" required>
        </label>
        <label class="check">
            <input type="checkbox" name="remember" value="1"> Remember this player
        </label>
        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif
        <button class="btn gold lg" type="submit">Enter</button>
        <a href="{{ route('register') }}">Create a new player</a>
    </form>
@endsection
