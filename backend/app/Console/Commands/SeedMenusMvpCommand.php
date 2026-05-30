<?php

namespace App\Console\Commands;

use Database\Seeders\Mvp\MenusMvpSeeder;
use Illuminate\Console\Command;

final class SeedMenusMvpCommand extends Command
{
    protected $signature = 'paqsuite:seed-menus-mvp';

    protected $description = 'Seed idempotente del catalogo pq_menus MVP (11 items)';

    public function handle(MenusMvpSeeder $menusMvpSeeder): int
    {
        $menusMvpSeeder->run();

        $this->info('Seed de menu MVP completado.');

        return self::SUCCESS;
    }
}
