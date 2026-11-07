(function () {
    const root = document.getElementById('game-root');
    if (!root) {
        return;
    }

    const gameKey = root.dataset.game;
    const slip = new Map();
    const placed = new Map();
    const listeners = [];
    const wheels = {};
    let chip = 0;
    let state = null;
    let lastSlip = [];
    let lastTicket = null;
    let skew = 0;
    let targetEpoch = Date.now();

    function money(value) {
        return Number(value || 0).toLocaleString('en-IN');
    }

    function formatClock(total) {
        const seconds = Math.max(0, Number(total) || 0);
        const minutes = Math.floor(seconds / 60);
        const remain = seconds % 60;
        return String(minutes).padStart(2, '0') + ':' + String(remain).padStart(2, '0');
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[char]));
    }

    function toast(message) {
        const el = document.getElementById('toast');
        if (!el) {
            return;
        }
        el.textContent = message;
        el.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => el.classList.remove('show'), 2800);
    }

    function headers() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    async function request(url, options) {
        const response = await fetch(url, options);
        const data = await response.json().catch(() => ({ message: 'Unexpected response.' }));
        if (!response.ok) {
            data.ok = false;
            if (!data.message && data.errors) {
                const first = Object.values(data.errors)[0];
                data.message = Array.isArray(first) ? first[0] : 'Check the ticket.';
            }
        }
        return data;
    }

    function pretty(type, selection) {
        const names = {
            andar: 'Andar',
            bahar: 'Bahar',
            jodi: 'Jodi',
            family: 'Family',
            spot: 'Number',
            single: 'Single',
            double: 'Double',
            triple: 'Triple',
            horse: 'Horse',
            color: 'Color',
            straight: 'Straight',
            box: 'Box',
            pair: 'Pair',
        };

        return (names[type] || type) + ' ' + selection;
    }

    function paintSlip() {
        const total = [...slip.values()].reduce((sum, bet) => sum + bet.amount, 0);
        const play = document.getElementById('play-total');
        if (play) {
            play.textContent = money(total);
        }

        const list = document.getElementById('slip-list');
        if (list) {
            list.replaceChildren();
            if (slip.size === 0) {
                list.textContent = lastTicket ? 'Last ticket ' + lastTicket : 'Select a chip, then a number.';
            }
            slip.forEach((bet, key) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.textContent = bet.label + ' · ' + money(bet.amount);
                item.addEventListener('click', () => {
                    slip.delete(key);
                    paintSlip();
                });
                list.appendChild(item);
            });
        }

        document.querySelectorAll('[data-bet-type]').forEach((button) => {
            const selection = button.dataset.selection;
            const key = button.dataset.betType + '|' + selection;
            const staged = slip.get(key)?.amount || 0;
            const down = placed.get(key) || 0;
            let extra = '';
            if (button.dataset.betType === 'jodi') {
                const familyStaged = slip.get('family|' + selection)?.amount || 0;
                const familyPlaced = placed.get('family|' + selection) || 0;
                if (familyPlaced || familyStaged) {
                    extra = ' F' + (familyPlaced + familyStaged);
                }
            }
            let text = '';
            if (down) {
                text += down;
            }
            if (staged) {
                text += (text ? '+' : '') + staged;
            }
            text += extra;
            let badge = button.querySelector('.stake-badge');
            if (!text) {
                badge?.remove();
                button.classList.remove('staked');
                return;
            }
            if (!badge) {
                badge = document.createElement('em');
                badge.className = 'stake-badge';
                button.appendChild(badge);
            }
            badge.textContent = text;
            button.classList.add('staked');
        });
    }

    function addBet(type, selection, label) {
        const key = type + '|' + selection;
        const next = (slip.get(key)?.amount || 0) + chip;
        if (!chip) {
            toast('Choose a chip first.');
            return;
        }
        if (next > 50000) {
            toast('Maximum 50,000 on one spot.');
            return;
        }
        slip.set(key, {
            type,
            selection,
            amount: next,
            label: label || pretty(type, selection),
        });
        paintSlip();
    }

    function bindChips() {
        const chips = [...document.querySelectorAll('[data-chip]')];
        if (!chips.length) {
            return;
        }
        chip = Number(chips[0].dataset.chip);
        chips[0].classList.add('selected');
        chips.forEach((button) => {
            button.addEventListener('click', () => {
                chip = Number(button.dataset.chip);
                chips.forEach((item) => item.classList.toggle('selected', item === button));
            });
        });
    }

    function bindSpots() {
        document.querySelectorAll('[data-bet-type]:not([data-manual])').forEach((button) => {
            button.addEventListener('click', () => {
                let type = button.dataset.betType;
                const selection = button.dataset.selection;
                if (type === 'jodi' && document.getElementById('family-toggle')?.checked) {
                    type = 'family';
                }
                addBet(type, selection);
            });
        });
    }

    function applyState(next) {
        state = next;
        if (next.server_now) {
            skew = new Date(next.server_now).getTime() - Date.now();
        }
        targetEpoch = Date.now() + (next.round?.seconds_left || 0) * 1000;
        lastSlip = next.last_slip || [];
        lastTicket = next.last_ticket || lastTicket;
        placed.clear();
        (next.my_bets || []).forEach((bet) => {
            const key = bet.type + '|' + bet.selection;
            placed.set(key, (placed.get(key) || 0) + bet.amount);
        });
        const balance = document.getElementById('balance');
        if (balance) {
            balance.textContent = money(next.balance);
        }
        const draw = document.getElementById('draw-time');
        if (draw && !draw.dataset.prefix) {
            draw.textContent = next.round?.draw_time || '--';
        }
        if (draw && draw.dataset.prefix) {
            draw.textContent = draw.dataset.prefix + ' ' + (next.round?.draw_time || '--');
        }
        const winValue = next.round_win > 0 ? next.round_win : (next.highlight_previous ? next.previous_win : 0);
        const win = document.getElementById('win-total');
        if (win) {
            win.textContent = money(winValue);
        }
        root.classList.toggle('locked', next.round?.status !== 'open');
        root.classList.toggle('winning', winValue > 0);
        paintSlip();
        listeners.forEach((listener) => listener(next));
    }

    async function refresh() {
        const data = await request('/api/games/' + gameKey, {
            headers: { 'Accept': 'application/json' },
        });
        if (data.ok) {
            applyState(data);
        }
    }

    async function play() {
        if (slip.size === 0) {
            toast('Place chips on the board first.');
            return;
        }
        if (state?.round?.status !== 'open') {
            toast('Betting is closed. Your slip stays for the next draw.');
            return;
        }
        const bets = [...slip.values()].map(({ type, selection, amount }) => ({ type, selection, amount }));
        const data = await request('/api/games/' + gameKey + '/play', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ bets }),
        });
        if (!data.ok) {
            toast(data.message || 'Bet rejected.');
            return;
        }
        slip.clear();
        lastTicket = data.ticket.code;
        toast('Ticket ' + data.ticket.code + ' accepted.');
        await refresh();
    }

    function clearSlip() {
        slip.clear();
        paintSlip();
    }

    function doubleUp() {
        if (slip.size === 0) {
            toast('Nothing to double yet.');
            return;
        }
        slip.forEach((bet) => {
            bet.amount = Math.min(50000, bet.amount * 2);
        });
        paintSlip();
    }

    function repeat() {
        if (!lastSlip.length) {
            toast('No previous ticket on this game.');
            return;
        }
        slip.clear();
        lastSlip.forEach((bet) => {
            slip.set(bet.type + '|' + bet.selection, {
                type: bet.type,
                selection: bet.selection,
                amount: bet.amount,
                label: pretty(bet.type, bet.selection),
            });
        });
        paintSlip();
    }

    async function claim(event) {
        event.preventDefault();
        const code = document.getElementById('ticket-code')?.value.trim();
        if (!code) {
            toast('Enter a ticket code.');
            return;
        }
        const data = await request('/api/tickets/claim', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ code }),
        });
        const pop = document.getElementById('claim-pop');
        const card = document.getElementById('claim-card');
        if (!data.ok) {
            card.innerHTML = '<h3>Ticket</h3><p>' + escapeHtml(data.message || 'Ticket not found.') + '</p><button type="button" id="claim-close">Close</button>';
        } else {
            const ticket = data.ticket;
            const rows = ticket.bets.map((bet) => '<li>' + escapeHtml(bet.type + ' ' + bet.selection) + ' · ' + escapeHtml(bet.amount) + ' · ' + escapeHtml(bet.status) + '</li>').join('');
            card.innerHTML = '<h3>' + escapeHtml(ticket.code) + '</h3><p>' + escapeHtml(ticket.game) + ' · ' + escapeHtml(ticket.draw_time || '') + '</p><ul>' + rows + '</ul><p>Stake ' + escapeHtml(money(ticket.stake)) + ' · Win ' + escapeHtml(money(ticket.win)) + ' · ' + escapeHtml(ticket.status) + '</p><button type="button" id="claim-close">Close</button>';
        }
        pop.hidden = false;
        document.getElementById('claim-close').addEventListener('click', () => {
            pop.hidden = true;
        });
        if (data.ok) {
            refresh();
        }
    }

    async function refill() {
        const data = await request('/api/wallet/refill', {
            method: 'POST',
            headers: headers(),
        });
        if (!data.ok) {
            toast(data.message || 'Refill is unavailable.');
            return;
        }
        toast('Added ' + money(data.added) + ' points.');
        await refresh();
    }

    function mountWheel(element, digits, palette) {
        if (!element) {
            return;
        }
        element.replaceChildren();
        const slice = 360 / digits.length;
        const stops = digits.map((digit, index) => palette[index % palette.length] + ' ' + (index * slice) + 'deg ' + ((index + 1) * slice) + 'deg');
        element.style.background = 'conic-gradient(' + stops.join(',') + ')';
        digits.forEach((digit, index) => {
            const spoke = document.createElement('div');
            spoke.className = 'spoke';
            spoke.style.transform = 'rotate(' + ((index + 0.5) * slice) + 'deg)';
            const label = document.createElement('span');
            label.textContent = digit;
            spoke.appendChild(label);
            element.appendChild(spoke);
        });
    }

    function landWheel(element, key, digit, slots, duration) {
        if (!element || digit === undefined || digit === null || digit === '') {
            return;
        }
        const slice = 360 / slots;
        const current = wheels[key] || 0;
        const mod = (360 - (Number(digit) + 0.5) * slice + 360) % 360;
        let target = current + 360 * (duration > 0 ? 5 : 0);
        target += (mod - (target % 360) + 360) % 360;
        if (duration > 0 && target <= current) {
            target += 360;
        }
        wheels[key] = target;
        element.style.transition = duration > 0 ? 'transform ' + duration + 'ms cubic-bezier(.12,.72,.08,1)' : 'none';
        element.style.transform = 'rotate(' + target + 'deg)';
    }

    function visibleResult(next) {
        if (next.round?.status === 'spinning') {
            return next.round.result;
        }
        if (next.highlight_previous) {
            return next.history?.[0]?.result || null;
        }
        return null;
    }

    function restingResult(next) {
        return next.round?.result || next.history?.[0]?.result || null;
    }

    function fit() {
        const scale = Math.max(0.01, Math.min(window.innerWidth / 1360, window.innerHeight / 820, 1.15));
        document.documentElement.style.setProperty('--fit', String(scale));
    }

    function tickClock() {
        const now = new Date(Date.now() + skew);
        const text = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Asia/Kolkata',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        }).format(now);
        const clock = document.getElementById('clock-now');
        if (clock) {
            clock.textContent = text;
        }
        const copy = document.getElementById('clock-copy');
        if (copy) {
            copy.textContent = text;
        }
        const left = Math.max(0, Math.round((targetEpoch - Date.now()) / 1000));
        const seconds = document.getElementById('seconds-left');
        if (seconds) {
            seconds.textContent = formatClock(left);
        }
    }

    document.querySelectorAll('.js-play').forEach((button) => button.addEventListener('click', play));
    document.querySelectorAll('.js-clear').forEach((button) => button.addEventListener('click', clearSlip));
    document.querySelectorAll('.js-double').forEach((button) => button.addEventListener('click', doubleUp));
    document.querySelectorAll('.js-repeat').forEach((button) => button.addEventListener('click', repeat));
    document.querySelectorAll('.js-print').forEach((button) => button.addEventListener('click', () => {
        if (!lastTicket) {
            toast('No ticket to print yet.');
            return;
        }
        window.open('/tickets/' + lastTicket, '_blank');
    }));
    document.querySelectorAll('.js-rules').forEach((button) => button.addEventListener('click', () => {
        document.getElementById('rules-pop').hidden = false;
    }));
    document.querySelectorAll('.js-close-pop').forEach((button) => button.addEventListener('click', () => {
        document.getElementById(button.dataset.pop).hidden = true;
    }));
    document.querySelector('.js-refill')?.addEventListener('click', refill);
    document.getElementById('claim-form')?.addEventListener('submit', claim);

    bindChips();
    bindSpots();
    fit();
    window.addEventListener('resize', fit);
    setInterval(tickClock, 250);
    paintSlip();

    window.Cabinet = {
        addBet,
        toast,
        money,
        mountWheel,
        landWheel,
        visibleResult,
        restingResult,
        onState(listener) {
            listeners.push(listener);
        },
        start() {
            refresh();
            setInterval(refresh, 1000);
        },
    };
})();
