<header class="topbar">
    <a class="lobby-link" href="{{ route('lobby') }}">Lobby</a>
    <strong class="game-tab">{{ $game['name'] }}</strong>
    <form class="ticket-form" id="claim-form">
        <input id="ticket-code" type="text" maxlength="16" placeholder="Enter ticket" autocomplete="off">
        <button type="submit" class="claim-btn">Claim</button>
    </form>
    <span class="welcome">Welcome, {{ auth()->user()->username }}</span>
    <span class="points">Points <b id="balance">{{ number_format(auth()->user()->balance) }}</b></span>
    <button type="button" class="mini js-refill">+10k</button>
    <button type="button" class="mini js-rules">Info</button>
    <a class="mini" href="{{ route('reports') }}">Report</a>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="mini">Exit</button>
    </form>
    <nav class="switcher">
        @foreach ($games as $key => $meta)
            <a href="{{ route('games.show', $key) }}" class="{{ $key === $gameKey ? 'on' : '' }}">{{ $meta['short'] }}</a>
        @endforeach
    </nav>
    <time id="clock-now">--</time>
</header>
