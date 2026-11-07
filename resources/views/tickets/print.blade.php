<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $ticket->code }}</title>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body class="receipt-body">
    <article class="receipt">
        <p class="kicker">For amusement only</p>
        <h1>{{ $gameName }}</h1>
        <p class="code">{{ $ticket->code }}</p>
        <p>Draw {{ $ticket->round?->locks_at?->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}</p>
        <p>Player {{ $ticket->user?->username ?? auth()->user()->username }}</p>
        <table>
            <thead>
                <tr><th>Bet</th><th>Pick</th><th>Stake</th><th>Odds</th><th>Win</th></tr>
            </thead>
            <tbody>
                @foreach ($ticket->bets as $bet)
                    <tr>
                        <td>{{ $bet->bet_type }}</td>
                        <td>{{ $bet->selection }}</td>
                        <td>{{ number_format($bet->amount) }}</td>
                        <td>{{ $bet->odds }}×</td>
                        <td>{{ $bet->status->value === 'pending' ? '…' : number_format($bet->payout) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p>Stake {{ number_format($ticket->stake) }} · Win {{ number_format($ticket->win) }} · {{ $ticket->status->value }}</p>
        <p>Points have no cash value.</p>
        <button type="button" onclick="window.print()">Print</button>
    </article>
</body>
</html>
