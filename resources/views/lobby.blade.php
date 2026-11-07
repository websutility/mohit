@extends('layouts.site')

@section('content')
    <section class="hero">
        <div>
            <p class="kicker">Eight cabinets · one points wallet</p>
            <h1>The same floor, in the browser.</h1>
            <p class="lead">Double Chance, Grand Chance, Triple Chance, Triple Fun, Spin 2 Win, Fun Target, Lucky Horse Race and Jackpot ABC. Every draw is timed, shared, and settled on the server.</p>
            @guest
                <a class="btn gold lg" href="{{ route('login') }}">Enter the floor</a>
            @endguest
        </div>
        <div class="hero-wheel" aria-hidden="true">
            <span>9</span><span>2</span><span>0</span><span>7</span><span>4</span><span>5</span><span>1</span><span>8</span><span>3</span><span>6</span>
            <b>92</b>
        </div>
    </section>
    <section class="game-grid">
        @foreach ($games as $key => $meta)
            <article class="game-card" style="--accent: {{ $meta['accent'] }}">
                <header>
                    <span>{{ $meta['short'] }}</span>
                    <h2>{{ $meta['name'] }}</h2>
                </header>
                <p>{{ $meta['summary'] }}</p>
                <ul>
                    @foreach ($meta['rules'] as $rule)
                        <li>{{ $rule }}</li>
                    @endforeach
                </ul>
                @auth
                    <a class="btn gold" href="{{ route('games.show', $key) }}">Open cabinet</a>
                @else
                    <a class="btn gold" href="{{ route('login') }}">Log in to play</a>
                @endauth
            </article>
        @endforeach
    </section>
@endsection
