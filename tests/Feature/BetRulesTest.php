<?php

namespace Tests\Feature;

use App\Exceptions\InvalidBetException;
use App\Services\Games\BetRules;
use Tests\TestCase;

class BetRulesTest extends TestCase
{
    public function test_double_chance_payouts_and_family(): void
    {
        $rules = app(BetRules::class);

        $this->assertSame('9.00', $rules->quote('double-chance', 'andar', '4'));
        $this->assertSame('9.00', $rules->quote('double-chance', 'bahar', '2'));
        $this->assertSame('90.00', $rules->quote('double-chance', 'jodi', '42'));
        $this->assertSame('22.00', $rules->quote('double-chance', 'family', '92'));
        $this->assertSame('90.00', $rules->quote('double-chance', 'family', '11'));

        $result = ['andar' => '2', 'bahar' => '9', 'jodi' => '29'];
        $this->assertTrue($rules->wins('double-chance', 'family', '92', $result));
        $this->assertTrue($rules->wins('double-chance', 'andar', '2', $result));
        $this->assertFalse($rules->wins('double-chance', 'jodi', '92', $result));
        $this->assertFalse($rules->wins('double-chance', 'family', '92', ['andar' => '9', 'bahar' => '3', 'jodi' => '93']));
        $this->assertSame(220, $rules->payout(10, '22.00'));
        $this->assertSame(900, $rules->payout(10, '90.00'));
    }

    public function test_triple_spot_and_jackpot_rules(): void
    {
        $rules = app(BetRules::class);

        $this->assertSame('9.00', $rules->quote('spin2win', 'spot', '7'));
        $this->assertTrue($rules->wins('fun-target', 'spot', '7', ['digit' => '7']));
        $this->assertSame('900.00', $rules->quote('triple-chance', 'triple', '007'));
        $this->assertTrue($rules->wins('triple-fun', 'double', '07', [
            'triple' => '007',
            'double' => '07',
            'single' => '7',
        ]));

        $this->assertSame('130.00', $rules->quote('jackpot-abc', 'box', '036'));
        $this->assertSame('250.00', $rules->quote('jackpot-abc', 'box', '007'));
        $this->assertSame('800.00', $rules->quote('jackpot-abc', 'box', '555'));
        $this->assertSame('800.00', $rules->quote('jackpot-abc', 'straight', '036'));
        $this->assertSame('80.00', $rules->quote('jackpot-abc', 'pair', 'ab:09'));
        $this->assertTrue($rules->wins('jackpot-abc', 'box', '036', ['A' => '6', 'B' => '0', 'C' => '3']));
        $this->assertTrue($rules->wins('jackpot-abc', 'straight', '036', ['A' => '0', 'B' => '3', 'C' => '6']));
        $this->assertFalse($rules->wins('jackpot-abc', 'straight', '036', ['A' => '6', 'B' => '0', 'C' => '3']));
        $this->assertTrue($rules->wins('jackpot-abc', 'pair', 'AB:09', ['A' => '0', 'B' => '9', 'C' => '4']));
        $this->assertTrue($rules->wins('jackpot-abc', 'spot', 'a3', ['A' => '3', 'B' => '1', 'C' => '8']));
    }

    public function test_horse_odds_follow_the_card(): void
    {
        $rules = app(BetRules::class);

        $this->assertSame('4.20', $rules->quote('horse-race', 'horse', 'rangeela'));
        $this->assertSame('1.80', $rules->quote('horse-race', 'color', 'RED'));
        $this->assertSame(42, $rules->payout(10, '4.20'));
        $this->assertSame(18, $rules->payout(10, '1.80'));
        $this->assertTrue($rules->wins('horse-race', 'horse', 'RANGEELA', [
            'horse' => 'RANGEELA',
            'number' => 8,
            'color' => 'red',
        ]));
        $this->assertTrue($rules->wins('horse-race', 'color', 'red', [
            'horse' => 'RANGEELA',
            'number' => 8,
            'color' => 'red',
        ]));
    }

    public function test_invalid_selections_are_rejected(): void
    {
        $rules = app(BetRules::class);

        $this->expectException(InvalidBetException::class);
        $rules->quote('double-chance', 'jodi', '4');
    }
}
