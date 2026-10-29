<?php

namespace Tests\Feature\Api\PedidosWeb;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsIntegracionMaestrosFeatureData;
use Tests\TestCase;

/**
 * @group integracion
 */
final class IntegracionMaestroCatalogoFeatureTest extends TestCase
{
    use SeedsIntegracionMaestrosFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIntegracionMaestrosFeature();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function testCondventaSnapshotEliminaAusenteNoReferenciado(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postMaestro('condventa', 'snapshot', [
            ['codigo' => 901, 'descripcion' => 'Contado'],
        ], $headers);

        $this->assertDatabaseMissing('pq_pedidosweb_condventa', ['codigo' => 902]);
    }

    public function testVendedoresSnapshotEliminaAusenteNoReferenciado(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postMaestro('vendedores', 'delta', [
            ['codVended' => 'V-SNAP-KEEP', 'nombre' => 'Se conserva'],
            ['codVended' => 'V-SNAP-DROP', 'nombre' => 'Se elimina'],
        ], $headers);

        $this->postMaestro('vendedores', 'snapshot', [
            ['codVended' => 'V-SNAP-KEEP', 'nombre' => 'Se conserva'],
        ], $headers);

        $this->assertDatabaseHas('pq_pedidosweb_vendedores', ['cod_vended' => 'V-SNAP-KEEP']);
        $this->assertDatabaseMissing('pq_pedidosweb_vendedores', ['cod_vended' => 'V-SNAP-DROP']);
    }

    public function testArticulosDeltaParcialYEliminacionReferenciada(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $receipt = $this->postMaestro('articulos', 'delta', [
            ['codigo' => 'ERP-OK', 'descripcion' => 'Artículo ERP', 'especial' => true],
            ['codigo' => null],
        ], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertSame(2, $resultado['procesados'] ?? null);

        $this->assertDatabaseHas('pq_pedidosweb_articulos', [
            'codigo' => 'ERP-OK',
            'especial' => true,
        ]);
    }

    public function testListapreciosPersisteArticulosConItemsAnidadosErp(): void
    {
        $this->requireIntegrationSchema();
        if (! Schema::hasTable('pq_pedidosweb_listaprecios_articulos')) {
            $this->markTestSkipped('Tabla pq_pedidosweb_listaprecios_articulos no disponible.');
        }

        $headers = $this->integrationHeaders();
        $codLista = 880001;

        $this->postMaestro('articulos', 'delta', [
            ['codigo' => 'LP-ART-1', 'descripcion' => 'Artículo lista precios'],
        ], $headers);

        $this->postMaestro('listaprecios', 'delta', [
            [
                'codigo' => $codLista,
                'descripcion' => 'Lista integración items[]',
                'items' => [
                    ['codigo' => 'LP-ART-1', 'precio' => 99.99],
                ],
            ],
        ], $headers);

        $this->assertDatabaseHas('pq_pedidosweb_listaprecios_articulos', [
            'cod_lista' => $codLista,
            'cod_articulo' => 'LP-ART-1',
        ]);
    }

    public function testEscalasPersisteDescripcionAlias(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postMaestro('escalas', 'delta', [
            [
                'codEscala' => 'E2',
                'descripcion' => 'Descripción vía alias',
                'nroEscala' => 2,
                'valores' => [
                    ['codValor' => 'U', 'descValor' => 'Unidad'],
                ],
            ],
        ], $headers);

        $this->assertDatabaseHas('pq_pedidosweb_escalas_cabecera', [
            'cod_escala' => 'E2',
            'descrip_es' => 'Descripción vía alias',
        ]);
    }

    public function testVendedorEliminarSiAusenteReferenciadoMarcaError(): void
    {
        $this->requireIntegrationSchema();
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            $this->markTestSkipped('Tabla clientes no disponible.');
        }

        $headers = $this->integrationHeaders();
        $codVended = 'V-DEL-REF';

        $this->postMaestro('vendedores', 'delta', [
            ['codVended' => $codVended, 'nombre' => 'Vendedor baja'],
        ], $headers);

        \Illuminate\Support\Facades\DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => 'CLI-VEND-DEL'],
            [
                'nombre' => 'Cliente ref vendedor',
                'cod_vended' => $codVended,
                'lista_precios' => 1,
                'cod_condvta' => 1,
            ],
        );

        $receipt = $this->postMaestro('vendedores', 'delta', [
            ['codVended' => $codVended, 'eliminarSiAusente' => true],
        ], $headers);

        $resultado = $this->getImportacionResultado((string) $receipt['importacionId'], $headers);
        $this->assertSame('completed', $resultado['estado'] ?? null);
        $this->assertGreaterThanOrEqual(1, $resultado['deletedErrors'] ?? 0);
        $this->assertDatabaseHas('pq_pedidosweb_vendedores', ['cod_vended' => $codVended]);
    }

    public function testEscalasPersisteCabeceraYDetalles(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postMaestro('escalas', 'delta', [
            [
                'codEscala' => 'E1',
                'descripEs' => 'Unidades',
                'nroEscala' => 1,
                'valores' => [
                    ['codValor' => 'U', 'descValor' => 'Unidad'],
                    ['codValor' => 'C', 'descValor' => 'Caja'],
                ],
            ],
        ], $headers);

        $this->assertDatabaseHas('pq_pedidosweb_escalas_detalle', [
            'cod_escala' => 'E1',
            'cod_valor' => 'U',
        ]);
    }

    public function testMedidasActualizaIdTecnicoPorCodigoNatural(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postMaestro('medidas', 'delta', [
            [
                'idMedida' => 990001,
                'codMedida' => 'D-UNIT',
                'descMedida' => 'Unidad inicial',
            ],
        ], $headers);

        $this->postMaestro('medidas', 'delta', [
            [
                'idMedida' => 990002,
                'codMedida' => 'D-UNIT',
                'descMedida' => 'Unidad actualizada',
            ],
        ], $headers);

        $this->assertDatabaseHas('pq_pedidosweb_medidas', [
            'id_medida' => 990002,
            'cod_medida' => 'D-UNIT',
            'desc_medida' => 'Unidad actualizada',
        ]);
    }

    public function testRecibeImportacionAsincronaDeMedidas(): void
    {
        $this->requireIntegrationSchema();

        $receipt = $this->postImportacionAsync(
            '/api/v1/integracion/maestros/medidas',
            $this->importacionBatch('delta', [[
                'idMedida' => 990003,
                'codMedida' => 'D-ASYNC',
                'descMedida' => 'Unidad asincrónica',
            ]], 'TEST-MEDIDAS-'.str_replace('.', '', (string) microtime(true))),
            $this->integrationHeaders(),
        );

        $this->assertSame(1, $receipt['numeroLote'] ?? null);
    }

    private function requireIntegrationSchema(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_integracion_maestros_stage')) {
            $this->markTestSkipped('SP/staging de integración no desplegados.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function postMaestro(string $recurso, string $modo, array $items, array $headers): array
    {
        return $this->postImportacionAsync(
            '/api/v1/integracion/maestros/'.$recurso,
            $this->importacionBatch($modo, $items),
            $headers,
        );
    }
}
