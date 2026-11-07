<div class="jackpot-board">
    <aside class="jp-history">
        <h3>Draws</h3>
        <div id="history"></div>
    </aside>
    <section class="jp-center">
        <div class="jp-live">
            <article data-live="A"><small>A</small><b>—</b></article>
            <article data-live="B"><small>B</small><b>—</b></article>
            <article data-live="C"><small>C</small><b>—</b></article>
            <article data-live="N"><small>N</small><b>—</b></article>
        </div>
        <p id="draw-time" data-prefix="Draw" class="jp-when">Draw --</p>
        <p class="jp-seconds"><span>Seconds</span> <b id="seconds-left">--</b></p>
        <p id="place-hint">Place bet please</p>
        <div class="meter-row">
            <div class="meter">Play <b id="play-total">0</b></div>
            <div class="meter win">Win <b id="win-total">0</b></div>
        </div>
    </section>
    <section class="jp-market">
        <div class="modes">
            <button type="button" class="mode on" data-mode="spot">Spot</button>
            <button type="button" class="mode" data-mode="straight">Str</button>
            <button type="button" class="mode" data-mode="box">Box</button>
            <button type="button" class="mode" data-mode="AB">AB</button>
            <button type="button" class="mode" data-mode="AC">AC</button>
            <button type="button" class="mode" data-mode="BC">BC</button>
        </div>
        @foreach (['A', 'B', 'C'] as $letter)
            <div class="jp-row">
                <span>{{ $letter }}</span>
                @for ($n = 0; $n < 10; $n++)
                    <button type="button" class="jp-cell" data-manual="1" data-letter="{{ $letter }}" data-digit="{{ $n }}">{{ $letter }}{{ $n }}</button>
                @endfor
            </div>
        @endforeach
        <div class="chips">
            @foreach ($game['chips'] as $chip)
                <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
            @endforeach
        </div>
        <div class="action-row">
            <button type="button" class="action" id="add-number">Add number</button>
            <button type="button" class="action js-clear">Clear</button>
            <button type="button" class="action js-repeat">Repeat</button>
            <button type="button" class="action js-double">Double</button>
            <button type="button" class="action js-play">Play</button>
            <button type="button" class="action js-print">Print</button>
        </div>
    </section>
</div>
