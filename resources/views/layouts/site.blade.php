<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Amusement Terminal' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>
    <header class="site-bar">
        <a class="logo" href="{{ route('lobby') }}">Amusement Terminal</a>
        <p class="ribbon">For amusement only. Points have no cash value.</p>
        <nav>
            @auth
                <span class="pill">{{ auth()->user()->username }} · <strong id="site-balance">{{ number_format(auth()->user()->balance) }}</strong> pts</span>
                <form method="post" action="{{ route('wallet.refill') }}" class="inline" id="site-refill">
                    @csrf
                    <button type="button" class="btn gold" id="site-refill-btn">Refill 10,000</button>
                </form>
                <a class="btn" href="{{ route('reports') }}">Reports</a>
                <form method="post" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn ghost">Log out</button>
                </form>
            @else
                <a class="btn" href="{{ route('login') }}">Log in</a>
                <a class="btn gold" href="{{ route('register') }}">Create player</a>
            @endauth
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    <script>
        document.getElementById('site-refill-btn')?.addEventListener('click', async () => {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(@json(route('wallet.refill')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();
            if (!response.ok) {
                alert(data.message || 'Refill is unavailable.');
                return;
            }
            const balance = document.getElementById('site-balance');
            if (balance) {
                balance.textContent = Number(data.balance).toLocaleString('en-IN');
            }
        });
    </script>
</body>
</html>
