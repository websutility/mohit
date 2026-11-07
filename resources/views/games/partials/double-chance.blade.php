<div class="split peach-board">
    <section class="left-board">
        <div class="chart-main">
            @for ($row = 0; $row < 10; $row++)
                <button type="button" class="all-btn" data-bet-type="andar" data-selection="{{ $row }}">ALL</button>
                @for ($col = 0; $col < 10; $col++)
                    @php $n = sprintf('%02d', $row * 10 + $col); @endphp
                    <button type="button" class="cell r{{ $row }}" data-bet-type="jodi" data-selection="{{ $n }}">{{ $n }}</button>
                @endfor
            @endfor
            <span class="all-spacer"></span>
            @for ($col = 0; $col < 10; $col++)
                <button type="button" class="all-btn" data-bet-type="bahar" data-selection="{{ $col }}">ALL</button>
            @endfor
        </div>
        <div class="family-row">
            <label><input type="checkbox" id="family-toggle"> Family</label>
            <div class="family-bar"></div>
            <div class="chips">
                @foreach ($game['chips'] as $chip)
                    <button type="button" class="chip" data-chip="{{ $chip }}">{{ $chip }}</button>
                @endforeach
            </div>
        </div>
        <div class="digit-board">
            <span class="side-label">Andar</span>
            @for ($n = 0; $n < 10; $n++)
                <button type="button" class="digit-key andar" data-bet-type="andar" data-selection="{{ $n }}">{{ $n }}</button>
            @endfor
            <span class="side-label">Bahar</span>
            @for ($n = 0; $n < 10; $n++)
                <button type="button" class="digit-key bahar" data-bet-type="bahar" data-selection="{{ $n }}">{{ $n }}</button>
            @endfor
        </div>
        <div class="action-row">
            <button type="button" class="action js-play">Play</button>
            <div class="meter">Play <b id="play-total">0</b></div>
            <div class="meter win">Win <b id="win-total">0</b></div>
            <button type="button" class="action js-clear">Clear</button>
            <button type="button" class="action js-repeat">Repeat</button>
            <button type="button" class="action js-double">Double</button>
            <button type="button" class="action js-print">Print</button>
        </div>
    </section>
    <aside class="wheel-panel checkered">
        <div class="draw-head">
            <div>
                <span>Current time</span>
                <strong id="clock-copy">--</strong>
            </div>
            <div>
                <span>Draw time</span>
                <strong id="draw-time">--</strong>
            </div>
            <div class="seconds-block">
                <span>Seconds left</span>
                <strong id="seconds-left">--</strong>
            </div>
        </div>
        <div class="wheel-wrap double">
            <div class="pointer"></div>
            <div class="wheel outer" id="wheel-outer"></div>
            <div class="wheel inner" id="wheel-inner"></div>
            <div class="hub" id="hub">--</div>
        </div>
        <div id="history" class="history-sheet"></div>
    </aside>
</div>
