<?php

namespace App\Services\Games;

use App\Exceptions\InvalidBetException;

class ResultFactory
{
    public function __construct(private GameCatalog $catalog) {}

    /**
     * @return array<string, mixed>
     */
    public function make(string $gameKey): array
    {
        return match ($gameKey) {
            'double-chance' => $this->doubleChance(),
            'grand-chance', 'spin2win', 'fun-target' => ['digit' => (string) random_int(0, 9)],
            'triple-chance', 'triple-fun' => $this->triple(),
            'horse-race' => $this->horse(),
            'jackpot-abc' => $this->jackpot(),
            default => throw new InvalidBetException('Unknown game.'),
        };
    }

    /**
     * @return array{andar: string, bahar: string, jodi: string}
     */
    private function doubleChance(): array
    {
        $andar = (string) random_int(0, 9);
        $bahar = (string) random_int(0, 9);

        return [
            'andar' => $andar,
            'bahar' => $bahar,
            'jodi' => $andar.$bahar,
        ];
    }

    /**
     * @return array{triple: string, double: string, single: string}
     */
    private function triple(): array
    {
        $triple = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        return [
            'triple' => $triple,
            'double' => substr($triple, 1, 2),
            'single' => substr($triple, 2, 1),
        ];
    }

    /**
     * @return array{horse: string, number: int, color: string}
     */
    private function horse(): array
    {
        $horses = $this->catalog->horses();
        $total = array_sum(array_column($horses, 'weight'));
        $roll = random_int(1, $total);
        $cursor = 0;

        foreach ($horses as $horse) {
            $cursor += $horse['weight'];

            if ($roll <= $cursor) {
                return [
                    'horse' => $horse['name'],
                    'number' => $horse['number'],
                    'color' => $horse['color'],
                ];
            }
        }

        $fallback = $horses[0];

        return [
            'horse' => $fallback['name'],
            'number' => $fallback['number'],
            'color' => $fallback['color'],
        ];
    }

    /**
     * @return array{A: string, B: string, C: string, jackpot: bool}
     */
    private function jackpot(): array
    {
        $a = (string) random_int(0, 9);
        $b = (string) random_int(0, 9);
        $c = (string) random_int(0, 9);

        return [
            'A' => $a,
            'B' => $b,
            'C' => $c,
            'jackpot' => $a === $b && $b === $c,
        ];
    }
}
