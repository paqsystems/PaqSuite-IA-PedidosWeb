<?php

namespace Tests\Feature;

use App\Models\PqExcelProceso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use Tests\TestCase;

final class SeedDeployCommandTest extends TestCase
{
    public function testSeedDeployIsIdempotentAndSeedsPedidoMasivo(): void
    {
        $this->artisan('paqsuite:seed-deploy', ['--skip-chat' => true])
            ->assertExitCode(0);

        $this->artisan('paqsuite:seed-deploy', ['--skip-chat' => true])
            ->assertExitCode(0);

        $this->assertTrue(
            PqExcelProceso::query()->where('codigo_proceso', 'PEDIDO_MASIVO')->where('activo', true)->exists()
        );
        $this->assertTrue(
            PqExcelProceso::query()->where('codigo_proceso', 'PEDIDO_INDIVIDUAL')->where('activo', true)->exists()
        );
    }

    public function testSeedDeployNoSobrescribeAtributosRolExistentes(): void
    {
        $rol = PqRol::query()->firstOrCreate(
            ['nombre_rol' => 'Vendedor'],
            ['descripcion_rol' => 'Perfil vendedor test', 'acceso_total' => false],
        );

        PqRolAtributo::query()->updateOrInsert(
            ['id_rol' => $rol->id, 'procedimiento' => 'pw_importacionmasiva'],
            [
                'id_rol' => $rol->id,
                'procedimiento' => 'pw_importacionmasiva',
                'permiso_alta' => true,
                'permiso_baja' => true,
                'permiso_modi' => true,
                'permiso_repo' => true,
            ],
        );

        $this->artisan('paqsuite:seed-deploy', [
            '--skip-chat' => true,
            '--skip-excel' => true,
            '--skip-menus' => true,
        ])->assertExitCode(0);

        $atributo = PqRolAtributo::query()
            ->where('id_rol', $rol->id)
            ->where('procedimiento', 'pw_importacionmasiva')
            ->firstOrFail();

        $this->assertTrue((bool) $atributo->permiso_alta);
        $this->assertTrue((bool) $atributo->permiso_modi);
    }
}
