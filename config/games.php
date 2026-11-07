<?php

return [

    'bet_seconds' => 45,

    'spin_seconds' => 8,

    'history_size' => 12,

    'max_spot' => 50000,

    'max_ticket' => 200000,

    'balance_cap' => 500000,

    'refill_amount' => 10000,

    'signup_bonus' => 10000,

    'horses' => [
        ['number' => 1, 'name' => 'CHETAK', 'odds' => 6.5, 'weight' => 12, 'color' => 'black'],
        ['number' => 2, 'name' => 'LUCKY', 'odds' => 7.0, 'weight' => 11, 'color' => 'red'],
        ['number' => 3, 'name' => 'BAAZIGAR', 'odds' => 8.0, 'weight' => 9, 'color' => 'black'],
        ['number' => 4, 'name' => 'JEET', 'odds' => 9.0, 'weight' => 8, 'color' => 'red'],
        ['number' => 5, 'name' => 'TIGER', 'odds' => 5.0, 'weight' => 16, 'color' => 'black'],
        ['number' => 6, 'name' => 'ROCKY', 'odds' => 12.0, 'weight' => 6, 'color' => 'red'],
        ['number' => 7, 'name' => 'TOOFAN', 'odds' => 14.0, 'weight' => 5, 'color' => 'black'],
        ['number' => 8, 'name' => 'RANGEELA', 'odds' => 4.2, 'weight' => 20, 'color' => 'red'],
        ['number' => 9, 'name' => 'ARJUN', 'odds' => 10.0, 'weight' => 7, 'color' => 'black'],
        ['number' => 10, 'name' => 'ROYAL', 'odds' => 5.5, 'weight' => 14, 'color' => 'red'],
        ['number' => 11, 'name' => 'TARZAN', 'odds' => 16.0, 'weight' => 4, 'color' => 'red'],
        ['number' => 12, 'name' => 'STAR', 'odds' => 20.0, 'weight' => 3, 'color' => 'black'],
    ],

    'catalog' => [
        'double-chance' => [
            'name' => 'Double Chance',
            'short' => 'Double',
            'theme' => 'peach',
            'accent' => '#e11d48',
            'view' => 'games.partials.double-chance',
            'summary' => 'Andar, Bahar and Jodi on a 00–99 chart.',
            'chips' => [2, 5, 10, 20, 50, 100, 200, 500],
            'rules' => [
                'Andar is the first digit and pays 9×.',
                'Bahar is the second digit and pays 9×.',
                'Jodi is the exact two-digit result and pays 90×.',
                'Family on 92 also covers 29, 99 and 22 and pays 22×. A repeated digit such as 11 pays 90×.',
                'Row ALL is an Andar bet. Column ALL is a Bahar bet.',
            ],
        ],
        'grand-chance' => [
            'name' => 'Grand Chance',
            'short' => 'Grand',
            'theme' => 'cosmos',
            'accent' => '#22d3ee',
            'view' => 'games.partials.grand-chance',
            'summary' => 'One wheel, digits 0–9, a 9× payout.',
            'chips' => [5, 10, 20, 50, 100, 500],
            'rules' => [
                'The wheel stops on one digit from 0 to 9.',
                'A winning number pays 9×.',
            ],
        ],
        'triple-chance' => [
            'name' => 'Triple Chance',
            'short' => 'Triple',
            'theme' => 'royal',
            'accent' => '#fbbf24',
            'view' => 'games.partials.triple',
            'summary' => 'Singles, doubles and triples from one 3-digit draw.',
            'chips' => [1, 5, 10, 50, 100, 500, 1000, 5000],
            'rules' => [
                'Each draw is a number from 000 to 999.',
                'Single is the last digit and pays 9×.',
                'Double is the last two digits and pays 90×.',
                'Triple is the full number and pays 900×.',
            ],
        ],
        'triple-fun' => [
            'name' => 'Triple Fun',
            'short' => 'Fun',
            'theme' => 'garden',
            'accent' => '#84cc16',
            'view' => 'games.partials.triple',
            'summary' => 'The same triple draw in a garden cabinet.',
            'chips' => [1, 5, 10, 50, 100, 500, 1000, 5000],
            'rules' => [
                'Each draw is a number from 000 to 999.',
                'Single is the last digit and pays 9×.',
                'Double is the last two digits and pays 90×.',
                'Triple is the full number and pays 900×.',
            ],
        ],
        'spin2win' => [
            'name' => 'Spin 2 Win',
            'short' => 'Spin',
            'theme' => 'midnight',
            'accent' => '#60a5fa',
            'view' => 'games.partials.spin2win',
            'summary' => 'A ten-slot wheel with a center result.',
            'chips' => [10, 20, 50, 100, 500],
            'rules' => [
                'The wheel stops on one digit from 0 to 9.',
                'A winning number pays 9×.',
            ],
        ],
        'fun-target' => [
            'name' => 'Fun Target',
            'short' => 'Target',
            'theme' => 'treasure',
            'accent' => '#f59e0b',
            'view' => 'games.partials.fun-target',
            'summary' => 'Drop chips on 0–9 and watch the target wheel.',
            'chips' => [1, 5, 10, 50, 100, 500, 1000, 5000],
            'rules' => [
                'The wheel stops on one digit from 0 to 9.',
                'A winning number pays 9×.',
                'Take stores the chips on the slip. Cancel clears them.',
            ],
        ],
        'horse-race' => [
            'name' => 'Lucky Horse Race',
            'short' => 'Horses',
            'theme' => 'derby',
            'accent' => '#16a34a',
            'view' => 'games.partials.horse-race',
            'summary' => 'Twelve horses. Win odds are printed on each card.',
            'chips' => [2, 4, 8, 10, 20, 50, 100],
            'rules' => [
                'Pick a horse to win. The card shows the payout multiple.',
                'Red and Black pay 1.8×.',
                'Favourites win more often. Longer odds win less often.',
            ],
        ],
        'jackpot-abc' => [
            'name' => 'Jackpot ABC',
            'short' => 'Jackpot',
            'theme' => 'sunset',
            'accent' => '#f97316',
            'view' => 'games.partials.jackpot',
            'summary' => 'Three reels, A B and C, plus straight, box and pair bets.',
            'chips' => [11, 22, 55, 110, 550, 1100],
            'rules' => [
                'A, B and C each draw a digit from 0 to 9.',
                'A spot such as A0 pays 9×.',
                'Straight pays 800× when A, B and C match in order.',
                'Box pays 130× for three different digits, 250× for a pair, and 800× for three of a kind.',
                'A pair bet on AB, AC or BC pays 80×.',
                'Three matching digits lights the jackpot marker.',
            ],
        ],
    ],

];
