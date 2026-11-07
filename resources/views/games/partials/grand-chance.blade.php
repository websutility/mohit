<div class="split cosmos-board">
    <section class="left-board">
        <div class="grand-pad">
            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 0] as $n)
                <button type="button" class="grand-key tone-{{ $n }}" data-bet-type="spot" data-selection="{{ $n }}">{{ $n }}</button>
            @endforeach
        </div>
        <div class="chips">
            @foreach ($game['chips'] as $chip)
                <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
            @endforeach
        </div>
        <div id="history" class="digit-strip"></div>
        <div class="place-row">
            <div>
                <b>Place your chips</b>
                <span id="play-total">0</span>
            </div>
            <div class="timer-box">
                <span>Timer left</span>
                <strong id="seconds-left">--</strong>
            </div>
        </div>
        <div class="action-row">
            <button type="button" class="action js-print">Print</button>
            <button type="button" class="action js-double">Double</button>
            <button type="button" class="action js-repeat">Rebet</button>
            <button type="button" class="action js-clear">Clear</button>
            <button type="button" class="action js-play">Play</button>
            <div class="meter win">Win <b id="win-total">0</b></div>
        </div>
    </section>
    <aside class="wheel-panel cosmos-wheel">
        <div class="brand-lockup">
            <span>Draw time</span>
            <strong id="draw-time">--</strong>
            <em>Grand Chance</em>
        </div>
        <div class="wheel-wrap single">
            <div class="pointer"></div>
            <div class="wheel" id="wheel"></div>
            <div class="hub gem" id="hub"></div>
        </div>
    </aside>
</div>
