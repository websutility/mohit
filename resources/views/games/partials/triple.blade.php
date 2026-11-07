<div class="triple-board">
    <header class="triple-head">
        <div class="score-pill">Score <b id="play-total">0</b></div>
        <div class="score-pill">Seconds <b id="seconds-left">--</b></div>
        <div class="score-pill">Winner <b id="hub">—</b></div>
        <div class="score-pill">Draw <b id="draw-time">--</b></div>
    </header>
    <div class="triple-columns">
        <section>
            <h3>Doubles</h3>
            <div class="scroll-grid doubles">
                @for ($n = 0; $n < 100; $n++)
                    <button type="button" data-bet-type="double" data-selection="{{ sprintf('%02d', $n) }}">{{ sprintf('%02d', $n) }}</button>
                @endfor
            </div>
            <p id="history-double" class="last-line"></p>
        </section>
        <section class="triple-center">
            <div class="wheel-wrap single compact">
                <div class="pointer"></div>
                <div class="wheel" id="wheel"></div>
                <div class="hub" id="hub-wheel">—</div>
            </div>
            <h3>Singles</h3>
            <div class="single-row">
                @for ($n = 0; $n < 10; $n++)
                    <button type="button" data-bet-type="single" data-selection="{{ $n }}">{{ $n }}</button>
                @endfor
            </div>
            <p id="history-single" class="last-line"></p>
        </section>
        <section>
            <h3>Triples</h3>
            <div class="scroll-grid triples">
                @for ($n = 0; $n < 1000; $n++)
                    <button type="button" data-bet-type="triple" data-selection="{{ sprintf('%03d', $n) }}">{{ sprintf('%03d', $n) }}</button>
                @endfor
            </div>
            <p id="history-triple" class="last-line"></p>
        </section>
    </div>
    <footer class="triple-foot">
        <div class="chips">
            @foreach ($game['chips'] as $chip)
                <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
            @endforeach
        </div>
        <div class="action-row">
            <button type="button" class="action js-repeat">Repeat</button>
            <button type="button" class="action js-double">Double</button>
            <button type="button" class="action js-print">Print</button>
            <button type="button" class="action js-clear">Clear</button>
            <button type="button" class="action js-play">Play</button>
            <div class="meter win">Win <b id="win-total">0</b></div>
        </div>
    </footer>
    <div id="history" hidden></div>
</div>
