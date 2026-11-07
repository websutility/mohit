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
        <div class="demo-row">
            <button type="button" class="btn" id="fill-pb">Use pb113</button>
            <button type="button" class="btn" id="fill-user">Use user_91</button>
        </div>
        <a href="{{ route('register') }}">Create a new player</a>
    </form>
    <script>
        function fill(name) {
            document.getElementById('username').value = name;
            document.getElementById('password').value = 'password';
        }
        document.getElementById('fill-pb').addEventListener('click', () => fill('pb113'));
        document.getElementById('fill-user').addEventListener('click', () => fill('user_91'));
    </script>
@endsection
