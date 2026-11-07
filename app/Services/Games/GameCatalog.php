<?php

namespace App\Services\Games;

class GameCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('games.catalog');
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $key): array
    {
        return $this->all()[$key];
    }

    /**
     * @return list<array{number: int, name: string, odds: float, weight: int, color: string}>
     */
    public function horses(): array
    {
        return config('games.horses');
    }

    /**
     * @return array{number: int, name: string, odds: float, weight: int, color: string}|null
     */
    public function horse(string $name): ?array
    {
        foreach ($this->horses() as $horse) {
            if ($horse['name'] === strtoupper($name)) {
                return $horse;
            }
        }

        return null;
    }
}
