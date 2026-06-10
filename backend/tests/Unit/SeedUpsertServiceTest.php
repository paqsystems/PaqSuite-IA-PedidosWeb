<?php

namespace Tests\Unit;

use App\Exceptions\SeedConflictException;
use App\Models\PqRol;
use App\Services\Seed\SeedUpsertService;
use Tests\TestCase;

final class SeedUpsertServiceTest extends TestCase
{
    public function testUpsertCreatesAndUpdatesWithoutConflict(): void
    {
        $service = new SeedUpsertService();

        $created = $service->upsertByNaturalKey(
            new PqRol(),
            ['nombre_rol' => 'TestRolUnit'],
            [
                'descripcion_rol' => 'Primera',
                'acceso_total' => false,
            ],
            ['descripcion_rol', 'acceso_total'],
        );

        $updated = $service->upsertByNaturalKey(
            new PqRol(),
            ['nombre_rol' => 'TestRolUnit'],
            [
                'descripcion_rol' => 'Primera',
                'acceso_total' => false,
            ],
            ['descripcion_rol', 'acceso_total'],
        );

        $this->assertSame($created->id, $updated->id);
        $this->assertSame(1, PqRol::query()->where('nombre_rol', 'TestRolUnit')->count());
    }

    public function testUpsertThrowsOnConflict(): void
    {
        $service = new SeedUpsertService();

        $service->upsertByNaturalKey(
            new PqRol(),
            ['nombre_rol' => 'TestRolConflict'],
            [
                'descripcion_rol' => 'Original',
                'acceso_total' => false,
            ],
        );

        $this->expectException(SeedConflictException::class);

        $service->upsertByNaturalKey(
            new PqRol(),
            ['nombre_rol' => 'TestRolConflict'],
            [
                'descripcion_rol' => 'Original',
                'acceso_total' => true,
            ],
            ['acceso_total'],
        );
    }
}
