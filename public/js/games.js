(function () {
    if (!window.Cabinet) {
        return;
    }

    const root = document.getElementById('game-root');
    const gameKey = root.dataset.game;
    const digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const rainbow = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#3b82f6', '#6366f1', '#a855f7', '#ec4899', '#f43f5e'];
    const blues = ['#1d4ed8', '#38bdf8', '#1e3a8a', '#67e8f9', '#2563eb', '#7dd3fc', '#1e40af', '#22d3ee', '#312e81', '#0ea5e9'];
    const golds = ['#fde68a', '#f59e0b', '#fcd34d', '#d97706', '#facc15', '#b45309', '#fde047', '#ea580c', '#fbbf24', '#92400e'];
    const shown = {};

    Cabinet.mountWheel(document.getElementById('wheel-outer'), digits, blues);
    Cabinet.mountWheel(document.getElementById('wheel-inner'), digits, golds);
    Cabinet.mountWheel(document.getElementById('wheel'), digits, rainbow);

    function spin(element, token, digit, slots, state) {
        if (!element || digit === undefined || digit === null) {
            return;
        }
        const mark = token + ':' + digit;
        if (shown[element.id] === mark) {
            return;
        }
        shown[element.id] = mark;
        const duration = state.round.status === 'spinning' ? Math.max(500, state.round.seconds_left * 1000) : 0;
        Cabinet.landWheel(element, element.id, digit, slots, duration);
    }

    function renderDouble(state) {
        const result = Cabinet.restingResult(state);
        const show = Cabinet.visibleResult(state);
        const hub = document.getElementById('hub');
        if (result && hub) {
            hub.textContent = (show || result).jodi;
            spin(document.getElementById('wheel-outer'), state.round.id + state.round.status, result.andar, 10, state);
            spin(document.getElementById('wheel-inner'), state.round.id + state.round.status, result.bahar, 10, state);
        }
        document.querySelectorAll('[data-bet-type]').forEach((button) => {
            let hit = false;
            if (show) {
                if (button.dataset.betType === 'andar') {
                    hit = show.andar === button.dataset.selection;
                }
                if (button.dataset.betType === 'bahar') {
                    hit = show.bahar === button.dataset.selection;
                }
                if (button.dataset.betType === 'jodi') {
                    hit = show.jodi === button.dataset.selection;
                }
            }
            button.classList.toggle('hit', hit);
        });
        const host = document.getElementById('history');
        const rows = (state.history || []).slice(0, 4);
        if (!rows.length) {
            host.textContent = 'Waiting for the first draw.';
            return;
        }
        const head = rows.map((row) => '<th>' + row.draw_time + '</th>').join('');
        const line = (label, pick) => '<tr><th>' + label + '</th>' + rows.map((row) => '<td>' + pick(row.result || {}) + '</td>').join('') + '</tr>';
        host.innerHTML = '<table><thead><tr><th>Time</th>' + head + '</tr></thead><tbody>'
            + line('Andar', (result) => result.andar || '')
            + line('Bahar', (result) => result.bahar || '')
            + line('Jodi', (result) => result.jodi || '')
            + '</tbody></table>';
    }

    function renderDigit(state) {
        const result = Cabinet.restingResult(state);
        const show = Cabinet.visibleResult(state);
        const digit = (show || result || {}).digit;
        const hub = document.getElementById('hub');
        if (hub && digit !== undefined) {
            hub.textContent = hub.classList.contains('chest') ? '★' : digit;
        }
        spin(document.getElementById('wheel'), state.round.id + state.round.status, digit, 10, state);
        document.querySelectorAll('[data-bet-type="spot"]').forEach((button) => {
            button.classList.toggle('hit', Boolean(show) && show.digit === button.dataset.selection);
        });
        const host = document.getElementById('history');
        if (!host) {
            return;
        }
        const rows = state.history || [];
        if (host.classList.contains('last10')) {
            host.textContent = 'Last 10  ' + rows.slice(0, 10).map((row) => row.result.digit).join('');
            return;
        }
        host.innerHTML = rows.slice(0, 12).map((row) => '<span>' + row.result.digit + '<small>' + row.draw_time + '</small></span>').join('');
    }

    function renderTriple(state) {
        const result = Cabinet.restingResult(state);
        const show = Cabinet.visibleResult(state);
        const visible = show || result;
        const hub = document.getElementById('hub');
        const center = document.getElementById('hub-wheel');
        if (hub) {
            hub.textContent = visible ? visible.triple : '—';
        }
        if (center) {
            center.textContent = visible ? visible.single : '—';
        }
        if (visible) {
            spin(document.getElementById('wheel'), state.round.id + state.round.status, visible.single, 10, state);
        }
        document.querySelectorAll('[data-bet-type]').forEach((button) => {
            const type = button.dataset.betType;
            button.classList.toggle('hit', Boolean(show) && show[type] === button.dataset.selection);
        });
        const history = state.history || [];
        document.getElementById('history-single').textContent = 'Last singles ' + history.slice(0, 5).map((row) => row.result.single).join('  ');
        document.getElementById('history-double').textContent = 'Last doubles ' + history.slice(0, 5).map((row) => row.result.double).join('  ');
        document.getElementById('history-triple').textContent = 'Last triples ' + history.slice(0, 5).map((row) => row.result.triple).join('  ');
    }

    function renderHorse(state) {
        const show = Cabinet.visibleResult(state);
        const result = show || Cabinet.restingResult(state);
        const banner = document.getElementById('winner-banner');
        if (banner) {
            banner.textContent = result ? result.number + '  ' + result.horse : 'Waiting for the off';
        }
        document.querySelectorAll('.horse-card').forEach((card) => {
            card.classList.toggle('hit', Boolean(result) && result.horse === card.dataset.selection && (show || state.highlight_previous || state.round.status !== 'open'));
        });
        document.querySelectorAll('[data-bet-type="color"]').forEach((button) => {
            button.classList.toggle('hit', Boolean(show) && show.color === button.dataset.selection);
        });
        const host = document.getElementById('history');
        host.innerHTML = (state.history || []).slice(0, 8).map((row) => '<span><b>' + row.result.number + '</b> ' + row.result.horse + '<small>' + row.draw_time + '</small></span>').join('');
    }

    function renderJackpot(state) {
        const show = Cabinet.visibleResult(state);
        const result = show || Cabinet.restingResult(state);
        ['A', 'B', 'C'].forEach((letter) => {
            const node = document.querySelector('[data-live="' + letter + '"] b');
            if (node) {
                node.textContent = result ? result[letter] : '—';
            }
        });
        const marker = document.querySelector('[data-live="N"] b');
        if (marker) {
            marker.textContent = result ? (result.jackpot ? 'JP' : 'N') : '—';
        }
        document.querySelectorAll('.jp-cell').forEach((cell) => {
            cell.classList.toggle('hit', Boolean(show) && show[cell.dataset.letter] === cell.dataset.digit);
        });
        const host = document.getElementById('history');
        host.innerHTML = (state.history || []).slice(0, 8).map((row) => {
            const item = row.result;
            return '<p><time>' + row.draw_time + '</time><span>A' + item.A + '</span><span>B' + item.B + '</span><span>C' + item.C + '</span></p>';
        }).join('');
    }

    const boards = {
        'double-chance': renderDouble,
        'grand-chance': renderDigit,
        'spin2win': renderDigit,
        'fun-target': renderDigit,
        'triple-chance': renderTriple,
        'triple-fun': renderTriple,
        'horse-race': renderHorse,
        'jackpot-abc': renderJackpot,
    };

    const pick = { A: null, B: null, C: null };
    let mode = 'spot';

    function paintPicks() {
        document.querySelectorAll('.jp-cell').forEach((cell) => {
            cell.classList.toggle('picked', pick[cell.dataset.letter] === cell.dataset.digit);
        });
    }

    function resetPick() {
        pick.A = null;
        pick.B = null;
        pick.C = null;
        paintPicks();
    }

    function pickReady() {
        if (mode === 'straight' || mode === 'box') {
            return pick.A && pick.B && pick.C;
        }
        if (mode === 'AB' || mode === 'AC' || mode === 'BC') {
            return pick[mode[0]] && pick[mode[1]];
        }
        return false;
    }

    function commitPick(announce) {
        if (!pickReady()) {
            if (announce) {
                Cabinet.toast('Finish the A, B and C pick first.');
            }
            return;
        }
        if (mode === 'straight' || mode === 'box') {
            Cabinet.addBet(mode, pick.A + pick.B + pick.C);
        } else {
            Cabinet.addBet('pair', mode + ':' + pick[mode[0]] + pick[mode[1]]);
        }
        resetPick();
    }

    document.querySelectorAll('.mode').forEach((button) => {
        button.addEventListener('click', () => {
            mode = button.dataset.mode;
            document.querySelectorAll('.mode').forEach((item) => item.classList.toggle('on', item === button));
            resetPick();
        });
    });

    document.querySelectorAll('.jp-cell').forEach((cell) => {
        cell.addEventListener('click', () => {
            if (mode === 'spot') {
                Cabinet.addBet('spot', cell.dataset.letter + cell.dataset.digit);
                return;
            }
            pick[cell.dataset.letter] = cell.dataset.digit;
            paintPicks();
            commitPick(false);
        });
    });

    document.getElementById('add-number')?.addEventListener('click', () => commitPick(true));

    if (boards[gameKey]) {
        Cabinet.onState(boards[gameKey]);
    }
    Cabinet.start();
})();
