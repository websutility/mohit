<div class="derby-board">
    <header class="derby-banner">
        <div>
            <span>Lucky Horse Race</span>
            <strong id="winner-banner">Waiting for the off</strong>
        </div>
        <div class="timer-box">
            <span>Draw <b id="draw-time">--</b></span>
            <strong id="seconds-left">--</strong>
        </div>
    </header>
    <div class="horse-grid">
        @foreach ($horses as $horse)
            <button type="button" class="horse-card coat-{{ $horse['color'] }}" data-bet-type="horse" data-selection="{{ $horse['name'] }}" data-number="{{ $horse['number'] }}">
                <i>{{ $horse['number'] }}</i>
                <svg viewBox="0 0 86 48" class="horse-svg" aria-hidden="true">
                    <ellipse cx="40" cy="28" rx="20" ry="9"/>
                    <path d="M54 24c8-1 16-12 18-18-6 3-9 9-8 10 3-7-2-12-7-13 3 6 0 11-4 13"/>
                    <circle cx="70" cy="8" r="1.6" fill="#111"/>
                    <path d="M24 32c-8 2-14 8-16 12M30 36l-2 10M40 38l1 10M50 37l4 11M58 32l7 12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                </svg>
                <b>{{ $horse['name'] }}</b>
                <em>{{ number_format($horse['odds'], 1) }}×</em>
            </button>
        @endforeach
    </div>
    <div class="derby-bottom">
        <div id="history" class="derby-results"></div>
        <div class="color-bets">
            <button type="button" class="color-btn red" data-bet-type="color" data-selection="red">Red 1.8×</button>
            <button type="button" class="color-btn black" data-bet-type="color" data-selection="black">Black 1.8×</button>
        </div>
        <div class="chips">
            @foreach ($game['chips'] as $chip)
                <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
            @endforeach
        </div>
        <div class="action-row">
            <button type="button" class="action js-clear">Clear</button>
            <button type="button" class="action js-repeat">Repeat</button>
            <button type="button" class="action js-double">Double</button>
            <button type="button" class="action js-play">Bet</button>
            <div class="meter">Play <b id="play-total">0</b></div>
            <div class="meter win">Win <b id="win-total">0</b></div>
            <button type="button" class="action js-print">Print</button>
        </div>
    </div>
</div>
