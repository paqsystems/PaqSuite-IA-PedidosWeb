<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

/**
 * @group integracion
 */
final class IntegracionMaestroClientesFeatureTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seedClientesDependencies();
    }

    public function testClientesDeltaIgnoraCodLoginYRecortaLeyendas(): void
    {
        $this->requireIntegrationSchema();

        $response = $this->postJson(
            '/api/v1/integracion/maestros/clientes',
            $this->batch('delta', [[
                'codClient' => 'D-22C',
                'codVended' => 'V-22C',
                'listaPrecios' => 1,
                'codCondvta' => 1,
                'codTranspor' => 'T-22C',
                'nombre' => 'Cliente 22c',
                'codLogin' => 'NO-DEBE-ESCRIBIR',
                'leyenda1' => str_repeat('á', 80),
            ]]),
            $this->integrationHeaders(),
        );

        $response->assertOk()
            ->assertJsonPath('resultado.aplicados', 1)
            ->assertJsonPath('resultado.rechazados', 0);

        $this->assertDatabaseHas('pq_pedidosweb_clientes', [
            'cod_client' => 'D-22C',
            'cod_login' => null,
            'leyenda_1' => str_repeat('á', 60),
        ]);
    }

    public function testClientesImportaDireccionesYContactosComoNodosHijos(): void
    {
        $this->requireIntegrationSchema();

        $response = $this->postJson(
            '/api/v1/integracion/maestros/clientes',
            $this->batch('delta', [[
                'codClient' => 'D-22C',
                'nombre' => 'Cliente 22c',
                'direcciones' => [[
                    'idDe' => 1,
                    'codDe' => 'DOM',
                    'direccion' => 'Dirección 22c',
                    'habitual' => true,
                ]],
                'contactos' => [[
                    'codContacto' => 'C-22C',
                    'nombre' => 'Contacto 22c',
                ]],
            ]]),
            $this->integrationHeaders(),
        )->assertOk()
            ->assertJsonPath('resultado.aplicados', 3)
            ->assertJsonPath('resultado.rechazados', 0);

        $this->assertDatabaseHas('pq_pedidosweb_clientesde', [
            'cod_client' => 'D-22C',
            'id_de' => 1,
            'habitual' => 'S',
        ]);
        $this->assertDatabaseHas('pq_pedidosweb_clientescontactos', [
            'cod_client' => 'D-22C',
            'cod_contacto' => 'C-22C',
        ]);
    }

    public function testBajaClienteEliminaPrimeroContactosYDirecciones(): void
    {
        $this->requireIntegrationSchema();
        $cliente = 'DEL-22C';

        $this->seedClient($cliente);
        DB::table('pq_pedidosweb_clientesde')->updateOrInsert(
            ['cod_client' => $cliente, 'id_de' => 1],
            ['direccion' => 'Dirección de baja', 'habitual' => 'N'],
        );
        DB::table('pq_pedidosweb_clientescontactos')->updateOrInsert(
            ['cod_client' => $cliente, 'cod_contacto' => 'C-DEL'],
            ['nombre' => 'Contacto de baja'],
        );

        $this->postJson(
            '/api/v1/integracion/maestros/clientes',
            $this->batch('delta', [[
                'codClient' => $cliente,
                'eliminarSiAusente' => true,
            ]]),
            $this->integrationHeaders(),
        )->assertOk()
            ->assertJsonPath('resultado.aplicados', 1)
            ->assertJsonPath('resultado.rechazados', 0);

        $this->assertDatabaseMissing('pq_pedidosweb_clientescontactos', ['cod_client' => $cliente]);
        $this->assertDatabaseMissing('pq_pedidosweb_clientesde', ['cod_client' => $cliente]);
        $this->assertDatabaseMissing('pq_pedidosweb_clientes', ['cod_client' => $cliente]);
    }

    public function testBajaClienteReferenciadoPorDeudaEsRechazadaSinBorrar(): void
    {
        $this->requireIntegrationSchema();
        $cliente = 'REF-22C';

        $this->seedClient($cliente);
        DB::table('pq_pedidosweb_deuda')->insert([
            'cod_cliente' => $cliente,
            't_comp' => 'FC',
            'n_comp' => '0001',
            'fecha_vto' => '2026-12-31',
            'saldo' => 10,
        ]);

        $this->postJson(
            '/api/v1/integracion/maestros/clientes',
            $this->batch('delta', [[
                'codClient' => $cliente,
                'eliminarSiAusente' => true,
            ]]),
            $this->integrationHeaders(),
        )->assertOk()
            ->assertJsonPath('resultado.aplicados', 0)
            ->assertJsonPath('resultado.rechazados', 1)
            ->assertJsonPath('resultado.errores.0.codigo', 'integracion.maestro.deleteReferenciado');

        $this->assertDatabaseHas('pq_pedidosweb_clientes', ['cod_client' => $cliente]);
    }

    public function testParametroFueraDeWhitelistEsRechazadoSinAbortarLote(): void
    {
        $this->requireIntegrationSchema();

        $this->postJson(
            '/api/v1/integracion/maestros/parametros',
            $this->batch('delta', [[
                'clave' => 'ClaveInventada',
                'valorString' => 'no permitido',
            ]]),
            $this->integrationHeaders(),
        )->assertOk()
            ->assertJsonPath('resultado.aplicados', 0)
            ->assertJsonPath('resultado.rechazados', 1)
            ->assertJsonPath('resultado.errores.0.codigo', 'integracion.maestro.parametroNoPermitido');
    }

    private function requireIntegrationSchema(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_integracion_maestros_stage')) {
            $this->markTestSkipped('SP/staging de integración no desplegados.');
        }
    }

    private function seedClientesDependencies(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_vendedores')) {
            return;
        }

        DB::table('pq_pedidosweb_vendedores')->updateOrInsert(
            ['cod_vended' => 'V-22C'],
            ['nombre' => 'Vendedor 22c', 'supervisor' => false],
        );
        DB::table('pq_pedidosweb_listaprecios')->updateOrInsert(
            ['cod_lista' => 1],
            ['incluye_iva' => false, 'moneda' => 1, 'descripcion' => 'Lista 22c', 'decimales' => 2],
        );
        DB::table('pq_pedidosweb_condventa')->updateOrInsert(
            ['codigo' => 1],
            ['descripcion' => 'Contado 22c'],
        );
        DB::table('pq_pedidosweb_transportes')->updateOrInsert(
            ['codigo' => 'T-22C'],
            ['descripcion' => 'Transporte 22c'],
        );
        $this->seedClient('D-22C');
    }

    private function seedClient(string $cliente): void
    {
        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => $cliente],
            [
                'nombre' => 'Cliente '.$cliente,
                'cod_vended' => 'V-22C',
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'cod_transpor' => 'T-22C',
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function batch(string $modo, array $items): array
    {
        return [
            'modo' => $modo,
            'fechaProceso' => '2026-10-22T12:00:00-03:00',
            'origen' => 'feature-test',
            'items' => $items,
        ];
    }
}
