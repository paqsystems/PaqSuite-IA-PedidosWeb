<?php

namespace App\Console\Commands;

use App\Exceptions\SeedConflictException;
use App\Services\Seed\MenuPrerequisiteValidator;
use Database\Seeders\Mvp\RolesMvpSeeder;
use Database\Seeders\Mvp\SecurityMvpSeeder;
use Illuminate\Console\Command;
use Throwable;

final class SeedSeguridadMvpCommand extends Command
{
    protected $signature = 'paqsuite:seed-seguridad-mvp';

    protected $description = 'Seed idempotente de roles, permisos, usuarios MVP y vinculos comerciales';

    public function handle(
        MenuPrerequisiteValidator $menuPrerequisiteValidator,
        RolesMvpSeeder $rolesMvpSeeder,
        SecurityMvpSeeder $securityMvpSeeder,
    ): int {
        try {
            $menuPrerequisiteValidator->assertMenuSeedReady();
            $rolesMvpSeeder->run();
            $securityMvpSeeder->run();
        } catch (SeedConflictException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Seed de seguridad MVP completado.');

        return self::SUCCESS;
    }
}
