<?php

namespace Tests\Feature;

use App\Models\GameRound;
use App\Models\User;
use App\Services\Games\RoundClock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GameplayTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_lobby_is_public_and_games_require_login(): void
    {
        $this->get(route('lobby'))->assertOk()->assertSee('Amusement Terminal');
        $this->get(route('login'))->assertOk()->assertSee('Welcome back')->assertDontSee('Use pb');
        $this->get(route('games.show', 'double-chance'))->assertRedirect(route('login'));
        $this->getJson(route('games.state', 'double-chance'))->assertUnauthorized();
    }

    public function test_every_cabinet_renders(): void
    {
        $user = User::factory()->create();

        foreach (array_keys(config('games.catalog')) as $key) {
            $this->actingAs($user)
                ->get(route('games.show', $key))
                ->assertOk()
                ->assertSee(config('games.catalog.'.$key.'.name'));
        }
    }

    public function test_players_can_register_and_log_in(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Asha',
            'username' => 'asha',
            'email' => 'asha@amusement.test',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ])->assertRedirect(route('lobby'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'asha',
            'balance' => 10000,
        ]);

        $this->post(route('logout'))->assertRedirect(route('lobby'));

        $this->post(route('login.store'), [
            'username' => 'asha',
            'password' => 'secret1',
        ])->assertRedirect(route('lobby'));
        $this->assertAuthenticated();
    }

    public function test_jodi_win_is_credited_after_the_spin(): void
    {
        $start = $this->freezeOpen();
        $user = User::factory()->create(['balance' => 100000]);

        $this->actingAs($user)
            ->postJson(route('games.play', 'double-chance'), [
                'bets' => [
                    ['type' => 'jodi', 'selection' => '42', 'amount' => 10],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('ticket.stake', 10);

        $user->refresh();
        $this->assertSame(99990, $user->balance);

        $this->travelToSpin($start);
        $round = $this->round('double-chance', $start);
        $round->result = ['andar' => '4', 'bahar' => '2', 'jodi' => '42'];
        $round->save();

        $this->travelAfterSpin($start);
        $this->actingAs($user)->getJson(route('games.state', 'double-chance'))->assertOk();

        $user->refresh();
        $this->assertSame(100890, $user->balance);
        $this->assertDatabaseHas('bets', [
            'user_id' => $user->id,
            'selection' => '42',
            'status' => 'won',
            'payout' => 900,
        ]);
    }

    public function test_closed_round_and_bad_bets_do_not_take_points(): void
    {
        $start = $this->freezeOpen();
        $user = User::factory()->create(['balance' => 50]);

        $this->actingAs($user)
            ->postJson(route('games.play', 'double-chance'), [
                'bets' => [
                    ['type' => 'jodi', 'selection' => '4', 'amount' => 10],
                ],
            ])
            ->assertStatus(422);

        $this->actingAs($user)
            ->postJson(route('games.play', 'horse-race'), [
                'bets' => [
                    ['type' => 'horse', 'selection' => 'NOPE', 'amount' => 10],
                ],
            ])
            ->assertStatus(422);

        $this->actingAs($user)
            ->postJson(route('games.play', 'double-chance'), [
                'bets' => [
                    ['type' => 'andar', 'selection' => '1', 'amount' => 100],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Not enough points.');

        Carbon::setTestNow(Carbon::createFromTimestamp($start + (int) config('games.bet_seconds') + 1));

        $this->actingAs($user)
            ->postJson(route('games.play', 'double-chance'), [
                'bets' => [
                    ['type' => 'andar', 'selection' => '1', 'amount' => 10],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Betting is closed for this draw.');

        $user->refresh();
        $this->assertSame(50, $user->balance);
    }

    public function test_horse_and_jackpot_tickets_settle(): void
    {
        $start = $this->freezeOpen();
        $user = User::factory()->create(['balance' => 100000]);

        $this->actingAs($user)->postJson(route('games.play', 'horse-race'), [
            'bets' => [
                ['type' => 'horse', 'selection' => 'RANGEELA', 'amount' => 10],
                ['type' => 'color', 'selection' => 'red', 'amount' => 10],
            ],
        ])->assertOk();

        $this->actingAs($user)->postJson(route('games.play', 'jackpot-abc'), [
            'bets' => [
                ['type' => 'straight', 'selection' => '036', 'amount' => 5],
                ['type' => 'spot', 'selection' => 'A0', 'amount' => 5],
            ],
        ])->assertOk();

        $this->travelToSpin($start);

        $horse = $this->round('horse-race', $start);
        $horse->result = ['horse' => 'RANGEELA', 'number' => 8, 'color' => 'red'];
        $horse->save();

        $jackpot = $this->round('jackpot-abc', $start);
        $jackpot->result = ['A' => '0', 'B' => '3', 'C' => '6', 'jackpot' => false];
        $jackpot->save();

        $this->travelAfterSpin($start);
        $this->actingAs($user)->getJson(route('games.state', 'horse-race'))->assertOk();
        $this->actingAs($user)->getJson(route('games.state', 'jackpot-abc'))->assertOk();

        $user->refresh();
        $stake = 10 + 10 + 5 + 5;
        $win = 42 + 18 + 4000 + 45;
        $this->assertSame(100000 - $stake + $win, $user->balance);
    }

    public function test_claim_refill_reports_and_unknown_game(): void
    {
        $start = $this->freezeOpen();
        $user = User::factory()->create(['balance' => 1000]);
        $other = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('games.play', 'grand-chance'), [
            'bets' => [
                ['type' => 'spot', 'selection' => '8', 'amount' => 20],
            ],
        ])->assertOk();

        $code = $response->json('ticket.code');

        $this->actingAs($other)
            ->postJson(route('tickets.claim'), ['code' => $code])
            ->assertNotFound();

        $this->actingAs($user)
            ->postJson(route('tickets.claim'), ['code' => $code])
            ->assertOk()
            ->assertJsonPath('ticket.status', 'open');

        $this->actingAs($user)->get(route('tickets.show', $code))->assertOk()->assertSee($code);
        $this->actingAs($other)->get(route('tickets.show', $code))->assertNotFound();

        $this->actingAs($user)
            ->postJson(route('wallet.refill'))
            ->assertOk()
            ->assertJsonPath('added', 10000);

        $user->refresh();
        $this->assertSame(1000 - 20 + 10000, $user->balance);

        $user->balance = (int) config('games.balance_cap');
        $user->save();

        $this->actingAs($user)
            ->postJson(route('wallet.refill'))
            ->assertStatus(422);

        $this->actingAs($user)->get(route('reports'))->assertOk()->assertSee('Grand Chance');
        $this->actingAs($user)->get(route('games.show', 'not-a-game'))->assertNotFound();
        $this->actingAs($user)->getJson(route('games.state', 'spin2win'))
            ->assertOk()
            ->assertJsonPath('round.status', 'open')
            ->assertJsonPath('round.result', null);

        $this->assertNotNull($start);
    }

    public function test_sync_command_creates_one_board_per_game(): void
    {
        $this->freezeOpen();

        $this->artisan('games:sync')->assertSuccessful();

        $expected = count(config('games.catalog')) * ((int) config('games.history_size') + 1);
        $this->assertSame($expected, GameRound::query()->count());

        $this->artisan('games:sync')->assertSuccessful();
        $this->assertSame($expected, GameRound::query()->count());
    }

    private function freezeOpen(): int
    {
        $cycle = app(RoundClock::class)->cycle();
        $start = intdiv(1_800_000_000, $cycle) * $cycle;
        Carbon::setTestNow(Carbon::createFromTimestamp($start + 5));

        return $start;
    }

    private function travelToSpin(int $start): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp($start + (int) config('games.bet_seconds') + 1));
        app(RoundClock::class)->syncAll();
    }

    private function travelAfterSpin(int $start): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp($start + app(RoundClock::class)->cycle() + 1));
    }

    private function round(string $game, int $start): GameRound
    {
        return GameRound::query()
            ->where('game_key', $game)
            ->where('round_index', intdiv($start, app(RoundClock::class)->cycle()))
            ->firstOrFail();
    }
}
