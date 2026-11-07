<div class="target-board">
    <header class="target-head">
        <div class="score-pill">Score <b id="play-total">0</b></div>
        <div>
            <span>Draw time <b id="draw-time">--</b></span>
            <span>Time <b id="seconds-left">--</b></span>
        </div>
        <div class="score-pill">Winner <b id="win-total">0</b></div>
    </header>
    <div class="target-stage">
        <div class="wheel-wrap single gold-wheel">
            <div class="pointer"></div>
            <div class="wheel" id="wheel"></div>
            <div class="hub chest" id="hub">★</div>
        </div>
        <p id="history" class="last10">Last 10</p>
    </div>
    <div class="chips">
        @foreach ($game['chips'] as $chip)
            <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
        @endforeach
    </div>
    <div class="target-spots">
        @for ($n = 0; $n < 10; $n++)
            <button type="button" class="target-spot" data-bet-type="spot" data-selection="{{ $n }}">{{ $n }}</button>
        @endfor
    </div>
    <div class="action-row">
        <button type="button" class="action js-play">Take</button>
        <button type="button" class="action js-clear">Cancel bet</button>
        <button type="button" class="action js-play">Bet ok</button>
        <button type="button" class="action js-print">Print</button>
        <button type="button" class="action js-repeat">Repeat</button>
        <button type="button" class="action js-double">Double</button>
    </div>
    <p class="amuse-line">For amusement only</p>
</div>
