<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $game['name'] }} · Amusement Terminal</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/terminal.css') }}">
</head>
<body class="terminal-body theme-{{ $game['theme'] }}">
    <div class="stage-sizer">
        <div class="stage" id="game-root" data-game="{{ $gameKey }}">
            @include('games.partials.topbar')
            <div id="slip-list" class="slip-list"></div>
            <div class="cabinet">
                @include($game['view'])
            </div>
            <div id="rules-pop" class="pop" hidden>
                <div class="pop-card">
                    <h3>{{ $game['name'] }}</h3>
                    <ul>
                        @foreach ($game['rules'] as $rule)
                            <li>{{ $rule }}</li>
                        @endforeach
                    </ul>
                    <p>Draws are shared. Betting closes when the timer hits zero, then the wheel settles. Points cannot be cashed out.</p>
                    <button type="button" class="js-close-pop" data-pop="rules-pop">Close</button>
                </div>
            </div>
            <div id="claim-pop" class="pop" hidden>
                <div class="pop-card" id="claim-card"></div>
            </div>
            <div id="toast" role="status"></div>
        </div>
    </div>
    <script src="{{ asset('js/terminal.js') }}"></script>
    <script src="{{ asset('js/games.js') }}"></script>
</body>
</html>
