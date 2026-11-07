@extends('layouts.site')

@section('content')
    <section class="panel">
        <h1>Tickets</h1>
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Game</th>
                    <th>Ticket</th>
                    <th>Stake</th>
                    <th>Win</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->created_at->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}</td>
                        <td>{{ $ticket->game_key }}</td>
                        <td><a href="{{ route('tickets.show', $ticket->code) }}">{{ $ticket->code }}</a></td>
                        <td>{{ number_format($ticket->stake) }}</td>
                        <td>{{ number_format($ticket->win) }}</td>
                        <td>{{ $ticket->status->value }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No tickets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    <section class="panel">
        <h1>Recent draws</h1>
        @foreach ($results as $block)
            <h2>{{ $block['name'] }}</h2>
            <ul class="draw-list">
                @foreach ($block['rounds'] as $round)
                    <li>
                        <time>{{ $round->locks_at->timezone(config('app.timezone'))->format('d/m h:i A') }}</time>
                        <x-result-text :result="$round->result" />
                    </li>
                @endforeach
            </ul>
        @endforeach
    </section>
@endsection
