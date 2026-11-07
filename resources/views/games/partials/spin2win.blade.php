<div class="split midnight-board">
    <section class="left-board">
        <div class="spin-top">
            <div class="timer-box tall">
                <span>Play clock</span>
                <strong id="seconds-left">--</strong>
            </div>
            <div class="wheel-wrap single">
                <div class="pointer"></div>
                <div class="wheel" id="wheel"></div>
                <div class="hub" id="hub">0</div>
            </div>
            <div class="brand-lockup">
                <em>Spin 2 Win</em>
                <span>Draw time</span>
                <strong id="draw-time">--</strong>
            </div>
        </div>
        <div id="history" class="history-pills"></div>
        <div class="spin-pad">
            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 0] as $n)
                <button type="button" data-bet-type="spot" data-selection="{{ $n }}">{{ $n }}</button>
            @endforeach
        </div>
        <div class="chips">
            @foreach ($game['chips'] as $chip)
                <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
            @endforeach
        </div>
        <div class="action-row">
            <button type="button" class="action js-rules">Info</button>
            <button type="button" class="action js-play">Advance</button>
            <button type="button" class="action js-double">Double</button>
            <button type="button" class="action js-repeat">Repeat</button>
            <button type="button" class="action js-clear">Clear</button>
            <button type="button" class="action js-play">Bet</button>
            <div class="meter">Play <b id="play-total">0</b></div>
            <div class="meter win">Win <b id="win-total">0</b></div>
        </div>
    </section>
</div>
