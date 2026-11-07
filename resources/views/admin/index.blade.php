@extends('layouts.site')

@section('content')
    <section class="panel">
        <h1>Floor desk</h1>
        <p>Add play points to a player. Points still have no cash value.</p>
        <form class="desk-form" method="post" action="{{ route('admin.credit') }}">
            @csrf
            <label>
                Player
                <select name="user_id" required>
                    @foreach ($players as $player)
                        <option value="{{ $player->id }}" @selected((int) old('user_id') === $player->id)>{{ $player->username }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Points
                <input name="amount" type="number" min="1" max="100000" value="{{ old('amount', 10000) }}" required>
            </label>
            <label>
                Note
                <input name="note" maxlength="80" value="{{ old('note', 'Desk credit') }}">
            </label>
            <button class="btn gold" type="submit">Add points</button>
        </form>
        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif
        <table>
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Points</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr>
                        <td>{{ $player->username }}</td>
                        <td>{{ number_format($player->balance) }}</td>
                        <td>{{ $player->is_admin ? 'Admin' : 'Player' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No players yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    <section class="panel">
        <h1>Latest tickets</h1>
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Player</th>
                    <th>Game</th>
                    <th>Ticket</th>
                    <th>Stake</th>
                    <th>Win</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->created_at?->timezone(config('app.timezone'))->format('d/m/Y h:i A') }}</td>
                        <td>{{ $ticket->user?->username }}</td>
                        <td>{{ $ticket->game_key }}</td>
                        <td><a href="{{ route('tickets.show', $ticket->code) }}">{{ $ticket->code }}</a></td>
                        <td>{{ number_format($ticket->stake) }}</td>
                        <td>{{ number_format($ticket->win) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No tickets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
