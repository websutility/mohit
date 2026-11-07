<?php

namespace App\Console\Commands;

use App\Services\Games\RoundClock;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('games:sync')]
#[Description('Advance draws and settle finished rounds')]
class SyncGamesCommand extends Command
{
    public function handle(RoundClock $clock): int
    {
        $clock->syncAll();
        $this->info('Draws synced.');

        return self::SUCCESS;
    }
}
