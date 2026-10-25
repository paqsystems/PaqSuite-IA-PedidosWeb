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

        $this->postJson('/api/v1/integracion/maestros/condventa', $this->batch('snapshot', [
            ['codigo' => 901, 'descripcion' => 'Contado'],
        ]), $headers)->assertOk();

        $this->assertDatabaseMissing('pq_pedidosweb_condventa', ['codigo' => 902]);
    }

    public function testArticulosDeltaParcialYEliminacionReferenciada(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $response = $this->postJson('/api/v1/integracion/maestros/articulos', $this->batch('delta', [
            ['codigo' => 'ERP-OK', 'descripcion' => 'Artículo ERP', 'especial' => true],
            ['codigo' => null],
        ]), $headers);

        $response->assertOk()
            ->assertJsonPath('resultado.aplicados', 1)
            ->assertJsonPath('resultado.rechazados', 1);

        $this->assertDatabaseHas('pq_pedidosweb_articulos', [
            'codigo' => 'ERP-OK',
            'especial' => true,
        ]);
    }

    public function testEscalasPersisteCabeceraYDetalles(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postJson('/api/v1/integracion/maestros/escalas', $this->batch('delta', [
            [
                'codEscala' => 'E1',
                'descripEs' => 'Unidades',
                'nroEscala' => 1,
                'valores' => [
                    ['codValor' => 'U', 'descValor' => 'Unidad'],
                    ['codValor' => 'C', 'descValor' => 'Caja'],
                ],
            ],
        ]), $headers)->assertOk();

        $this->assertDatabaseHas('pq_pedidosweb_escalas_detalle', [
            'cod_escala' => 'E1',
            'cod_valor' => 'U',
        ]);
    }

    public function testMedidasActualizaIdTecnicoPorCodigoNatural(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postJson('/api/v1/integracion/maestros/medidas', $this->batch('delta', [
            [
                'idMedida' => 990001,
                'codMedida' => 'D-UNIT',
                'descMedida' => 'Unidad inicial',
            ],
        ]), $headers)->assertOk();

        $this->postJson('/api/v1/integracion/maestros/medidas', $this->batch('delta', [
            [
                'idMedida' => 990002,
                'codMedida' => 'D-UNIT',
                'descMedida' => 'Unidad actualizada',
            ],
        ]), $headers)
            ->assertOk()
            ->assertJsonPath('resultado.aplicados', 1);

        $this->assertDatabaseHas('pq_pedidosweb_medidas', [
            'id_medida' => 990002,
            'cod_medida' => 'D-UNIT',
            'desc_medida' => 'Unidad actualizada',
        ]);
    }

    public function testRecibeImportacionAsincronaDeMedidas(): void
    {
        $this->requireIntegrationSchema();

        $response = $this->postJson('/api/v1/integracion/importaciones/medidas', [
            'importacionId' => 'TEST-MEDIDAS-'.str_replace('.', '', (string) microtime(true)),
            'numeroLote' => 1,
            'ultimoEnvio' => true,
            'modo' => 'delta',
            'fechaProceso' => '2026-10-21T12:00:00-03:00',
            'origen' => 'feature-test',
            'items' => [[
                'idMedida' => 990003,
                'codMedida' => 'D-ASYNC',
                'descMedida' => 'Unidad asincrónica',
            ]],
        ], $this->integrationHeaders());

        $response->assertStatus(202)
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.numeroLote', 1);
    }

    public function testListaArticuloSnapshotEsRechazado(): void
    {
        $this->requireIntegrationSchema();
        $headers = $this->integrationHeaders();

        $this->postJson('/api/v1/integracion/maestros/listaprecios-articulos', $this->batch('snapshot', [
            ['codLista' => 1, 'codArticulo' => 'ERP-OK', 'precio' => 10],
        ]), $headers)
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'integracion.maestro.modoNoSoportado');
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
    private function batch(string $modo, array $items): array
    {
        return [
            'modo' => $modo,
            'fechaProceso' => '2026-10-21T12:00:00-03:00',
            'origen' => 'feature-test',
            'items' => $items,
        ];
    }
}
