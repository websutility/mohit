<?php

namespace App\Services\Games;

use App\Exceptions\InvalidBetException;

class BetRules
{
    public function __construct(private GameCatalog $catalog) {}

    public function quote(string $gameKey, string $type, string $selection): string
    {
        $selection = $this->normalize($gameKey, $type, $selection);

        return match ($gameKey) {
            'double-chance' => $this->quoteDouble($type, $selection),
            'grand-chance', 'spin2win', 'fun-target' => $this->quoteSpot($type, $selection),
            'triple-chance', 'triple-fun' => $this->quoteTriple($type, $selection),
            'horse-race' => $this->quoteHorse($type, $selection),
            'jackpot-abc' => $this->quoteJackpot($type, $selection),
            default => throw new InvalidBetException('Unknown game.'),
        };
    }

    public function normalize(string $gameKey, string $type, string $selection): string
    {
        $selection = trim($selection);

        if ($gameKey === 'horse-race' && $type === 'color') {
            return strtolower($selection);
        }

        if ($gameKey === 'horse-race' && $type === 'horse') {
            return strtoupper($selection);
        }

        if ($gameKey === 'jackpot-abc') {
            return strtoupper($selection);
        }

        return $selection;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function wins(string $gameKey, string $type, string $selection, array $result): bool
    {
        $selection = $this->normalize($gameKey, $type, $selection);

        return match ($gameKey) {
            'double-chance' => $this->winsDouble($type, $selection, $result),
            'grand-chance', 'spin2win', 'fun-target' => $type === 'spot' && (string) ($result['digit'] ?? '') === $selection,
            'triple-chance', 'triple-fun' => in_array($type, ['single', 'double', 'triple'], true) && (string) ($result[$type] ?? '') === $selection,
            'horse-race' => $this->winsHorse($type, $selection, $result),
            'jackpot-abc' => $this->winsJackpot($type, $selection, $result),
            default => false,
        };
    }

    public function payout(int $amount, string $odds): int
    {
        return (int) round((float) bcmul((string) $amount, $odds, 4));
    }

    /**
     * @return list<string>
     */
    public function familyMembers(string $jodi): array
    {
        $left = $jodi[0];
        $right = $jodi[1];
        $members = array_values(array_unique([$left.$left, $right.$right, $left.$right, $right.$left]));
        sort($members);

        return $members;
    }

    private function quoteDouble(string $type, string $selection): string
    {
        if ($type === 'andar' || $type === 'bahar') {
            $this->assertDigits($selection, 1);

            return '9.00';
        }

        if ($type === 'jodi') {
            $this->assertDigits($selection, 2);

            return '90.00';
        }

        if ($type === 'family') {
            $this->assertDigits($selection, 2);

            return count($this->familyMembers($selection)) === 1 ? '90.00' : '22.00';
        }

        throw new InvalidBetException('That bet is not on Double Chance.');
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function winsDouble(string $type, string $selection, array $result): bool
    {
        if ($type === 'andar') {
            return (string) ($result['andar'] ?? '') === $selection;
        }

        if ($type === 'bahar') {
            return (string) ($result['bahar'] ?? '') === $selection;
        }

        if ($type === 'jodi') {
            return (string) ($result['jodi'] ?? '') === $selection;
        }

        if ($type === 'family') {
            return in_array((string) ($result['jodi'] ?? ''), $this->familyMembers($selection), true);
        }

        return false;
    }

    private function quoteSpot(string $type, string $selection): string
    {
        if ($type !== 'spot') {
            throw new InvalidBetException('Pick a number from 0 to 9.');
        }

        $this->assertDigits($selection, 1);

        return '9.00';
    }

    private function quoteTriple(string $type, string $selection): string
    {
        if ($type === 'single') {
            $this->assertDigits($selection, 1);

            return '9.00';
        }

        if ($type === 'double') {
            $this->assertDigits($selection, 2);

            return '90.00';
        }

        if ($type === 'triple') {
            $this->assertDigits($selection, 3);

            return '900.00';
        }

        throw new InvalidBetException('Pick a single, double, or triple.');
    }

    private function quoteHorse(string $type, string $selection): string
    {
        if ($type === 'horse') {
            $horse = $this->catalog->horse($selection);

            if ($horse === null) {
                throw new InvalidBetException('Unknown horse.');
            }

            return number_format((float) $horse['odds'], 2, '.', '');
        }

        if ($type === 'color') {
            if (! in_array($selection, ['red', 'black'], true)) {
                throw new InvalidBetException('Pick red or black.');
            }

            return '1.80';
        }

        throw new InvalidBetException('That bet is not on the horse race.');
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function winsHorse(string $type, string $selection, array $result): bool
    {
        if ($type === 'horse') {
            return (string) ($result['horse'] ?? '') === $selection;
        }

        if ($type === 'color') {
            return (string) ($result['color'] ?? '') === $selection;
        }

        return false;
    }

    private function quoteJackpot(string $type, string $selection): string
    {
        if ($type === 'spot') {
            $this->assertSpot($selection);

            return '9.00';
        }

        if ($type === 'straight') {
            $this->assertDigits($selection, 3);

            return '800.00';
        }

        if ($type === 'box') {
            $this->assertDigits($selection, 3);
            $unique = count(array_unique(str_split($selection)));

            return match ($unique) {
                1 => '800.00',
                2 => '250.00',
                default => '130.00',
            };
        }

        if ($type === 'pair') {
            if (! preg_match('/^(AB|AC|BC):[0-9]{2}$/', $selection)) {
                throw new InvalidBetException('Pick a valid pair.');
            }

            return '80.00';
        }

        throw new InvalidBetException('That bet is not on Jackpot ABC.');
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function winsJackpot(string $type, string $selection, array $result): bool
    {
        if ($type === 'spot') {
            return (string) ($result[$selection[0]] ?? '') === $selection[1];
        }

        $drawn = (string) ($result['A'] ?? '').(string) ($result['B'] ?? '').(string) ($result['C'] ?? '');

        if ($type === 'straight') {
            return $drawn === $selection;
        }

        if ($type === 'box') {
            return $this->signature($drawn) === $this->signature($selection);
        }

        if ($type === 'pair') {
            [$side, $digits] = explode(':', $selection);

            return (string) ($result[$side[0]] ?? '') === $digits[0]
                && (string) ($result[$side[1]] ?? '') === $digits[1];
        }

        return false;
    }

    private function assertDigits(string $selection, int $length): void
    {
        if (! preg_match('/^[0-9]{'.$length.'}$/', $selection)) {
            throw new InvalidBetException('Pick a valid number.');
        }
    }

    private function assertSpot(string $selection): void
    {
        if (! preg_match('/^[ABC][0-9]$/', $selection)) {
            throw new InvalidBetException('Pick a spot such as A0 or C6.');
        }
    }

    private function signature(string $digits): string
    {
        $chars = str_split($digits);
        sort($chars);

        return implode('', $chars);
    }
}
