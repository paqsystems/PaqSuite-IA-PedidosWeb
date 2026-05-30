<?php

namespace Database\Seeders\Mvp;

use App\Models\PqRol;
use App\Services\Seed\SeedUpsertService;
use Illuminate\Database\Seeder;

final class RolesMvpSeeder extends Seeder
{
    public function __construct(
        private readonly SeedUpsertService $seedUpsertService,
    ) {}

    public function run(): void
    {
        foreach (config('paqsuite_mvp.roles', []) as $role) {
            $this->seedUpsertService->upsertByNaturalKey(
                new PqRol(),
                ['nombre_rol' => $role['nombreRol']],
                [
                    'descripcion_rol' => $role['descripcionRol'],
                    'acceso_total' => $role['accesoTotal'],
                ],
                ['acceso_total'],
            );
        }
    }
}
