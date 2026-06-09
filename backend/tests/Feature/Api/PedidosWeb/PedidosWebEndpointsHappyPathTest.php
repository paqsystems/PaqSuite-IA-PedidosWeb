<?php

namespace Tests\Feature\Api\PedidosWeb;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\TestCase;

final class PedidosWebEndpointsHappyPathTest extends TestCase
{
    use SeedsPedidosWebFeatureData;

    private const SUPERVISOR = 'supervisor.mvp';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPedidosWebFeature();
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function readOnlyEndpointsProvider(): iterable
    {
        yield 'consultas pedidos ingresados' => ['/api/v1/consultas/pedidos-ingresados'];
        yield 'consultas pedidos pendientes' => ['/api/v1/consultas/pedidos-pendientes'];
        yield 'consultas presupuestos' => ['/api/v1/consultas/presupuestos'];
        yield 'consultas stock' => ['/api/v1/consultas/stock'];
        yield 'consultas deuda' => ['/api/v1/consultas/deuda'];
        yield 'consultas cheques' => ['/api/v1/consultas/cheques'];
        yield 'consultas historial ventas' => ['/api/v1/consultas/historial-ventas'];
        yield 'consultas detalle pedidos' => ['/api/v1/consultas/detalle-pedidos'];
        yield 'config parametros consulta' => ['/api/v1/config/parametros?programa=PedidosWeb'];
        yield 'motivos cierre' => ['/api/v1/motivos-cierre'];
        yield 'integracion logs' => ['/api/v1/integracion/logs'];
        yield 'dashboard operativo' => ['/api/v1/dashboard/operativo'];
        yield 'dashboard resumen mensual' => ['/api/v1/dashboard/resumen-mensual'];
    }

    #[Test]
    #[DataProvider('readOnlyEndpointsProvider')]
    public function readOnlyEndpointReturns200(string $path): void
    {
        $this->getJson($path, $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0);
    }

    #[Test]
    public function consultasComprobantesIncluyenNombreFantasiaYFechaProcesoMinutos(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PHPCC');
        $this->insertComprobanteConDetalle($codPedido, 0);

        $response = $this->getJson('/api/v1/consultas/pedidos-ingresados', $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'resultado' => [
                    'items' => [['nombreFantasia']],
                    'metadata' => ['fecha_proceso'],
                ],
            ]);

        $fechaProceso = (string) $response->json('resultado.metadata.fecha_proceso');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fechaProceso);

        $item = collect($response->json('resultado.items'))
            ->firstWhere('codPedido', $codPedido);

        $this->assertNotNull($item);
        $this->assertSame('Cliente MVP', $item['nombreFantasia']);
    }

    #[Test]
    public function pedidosPendientesPermiteCopiarConPermisoAlta(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PHPPEN');
        $this->insertComprobanteConDetalle($codPedido, 1);

        $response = $this->getJson('/api/v1/consultas/pedidos-pendientes', $this->authHeadersFor(self::SUPERVISOR));

        $response->assertOk()->assertJsonPath('error', 0);

        $item = collect($response->json('resultado.items'))
            ->firstWhere('codPedido', $codPedido);

        $this->assertNotNull($item);
        $this->assertTrue($item['puedeCopiar']);
    }

    #[Test]
    public function consultaDetallePedidosIncluyePrecioNeto(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PHPDET');
        $this->insertComprobanteConDetalle($codPedido, 0);

        $response = $this->getJson('/api/v1/consultas/detalle-pedidos', $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'resultado' => [
                    'items' => [['precioNeto']],
                ],
            ]);

        $item = collect($response->json('resultado.items'))
            ->firstWhere('codPedido', $codPedido);

        $this->assertNotNull($item);
        $this->assertSame(150.0, (float) $item['precioNeto']);
    }

    #[Test]
    public function dashboardOperativoIncluyeUnidadesEnKpis(): void
    {
        $this->getJson('/api/v1/dashboard/operativo', $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'resultado' => [
                    'presupuestosActivos' => ['cantidad', 'importe', 'unidades'],
                    'pedidosIngresados' => ['cantidad', 'importe', 'unidades'],
                    'pedidosPendientes' => ['cantidad', 'importe', 'unidades'],
                ],
            ]);
    }

    #[Test]
    public function dashboardResumenMensualRetornaEstructuraPorEstado(): void
    {
        $this->getJson('/api/v1/dashboard/resumen-mensual', $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'resultado' => [
                    'anio',
                    'mes',
                    'porEstado' => [
                        '*' => ['estado', 'cantidad', 'importe', 'unidades'],
                    ],
                    'fechaCalculo',
                ],
            ]);
    }

    #[Test]
    public function comprobanteGrabarPedidoReturns200(): void
    {
        $payload = [
            'accionGrabacion' => 'pedido',
            ...$this->sampleGrabacionPayload(),
        ];

        $response = $this->postJson('/api/v1/comprobantes/grabar', $payload, $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 0)
            ->assertJsonStructure([
                'resultado' => ['cod_pedido', 'estado', 'nro_visible', 'total', 'total_iva', 'guidSufijo', 'mailEnviado'],
            ]);
    }

    #[Test]
    public function comprobanteGrabarPersisteCabeceraComercial(): void
    {
        $payload = [
            'accionGrabacion' => 'pedido',
            'cabecera' => [
                'cod_cliente' => 'CLIMVP001',
                'cod_vended' => 'VENACOT01',
                'cod_condvta' => 1,
                'cod_transpor' => 'MVP',
                'id_de' => 1,
                'lista_precios' => 1,
                'cod_perfil' => 'MVP',
            ],
            'renglones' => [
                [
                    'cod_articulo' => 'ART-HP-001',
                    'descripcion_articulo' => 'Articulo feature test',
                    'cantidad' => 1,
                    'precio' => 100,
                    'porc_bonif' => 0,
                    'porc_iva' => 21,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/comprobantes/grabar', $payload, $this->authHeadersFor(self::SUPERVISOR));
        $response->assertOk()->assertJsonPath('error', 0);

        $codPedido = (string) $response->json('resultado.cod_pedido');
        $cabecera = \Illuminate\Support\Facades\DB::table('pq_pedidosweb_pedidoscabecera')
            ->where('cod_pedido', $codPedido)
            ->first();

        $this->assertNotNull($cabecera);
        $this->assertSame('VENACOT01', $cabecera->cod_vended);
        $this->assertSame(1, (int) $cabecera->cod_condvta);
        $this->assertSame('MVP', $cabecera->cod_transpor);
        $this->assertSame(1, (int) $cabecera->id_de);
        $this->assertSame(1, (int) $cabecera->lista_precios);
        $this->assertSame('MVP', $cabecera->cod_perfil);
    }

    #[Test]
    public function pedidosStoreReturns200(): void
    {
        $response = $this->postJson('/api/v1/pedidos', $this->sampleGrabacionPayload(), $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 0);
    }

    #[Test]
    public function presupuestosStoreReturns200(): void
    {
        $response = $this->postJson('/api/v1/presupuestos', $this->sampleGrabacionPayload(), $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 99);
    }

    #[Test]
    public function pedidosShowReturns200(): void
    {
        $codPedido = $this->uniqueComprobanteCod('PHPSHOW');
        $this->insertComprobanteConDetalle($codPedido, 0);

        $response = $this->getJson("/api/v1/pedidos/{$codPedido}", $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.cabecera.cod_pedido', $codPedido);

        $this->assertNotEmpty($response->json('resultado.detalle'));
    }

    #[Test]
    public function presupuestosShowReturns200(): void
    {
        $codPresupuesto = $this->uniqueComprobanteCod('PRESHOW');
        $this->insertComprobanteConDetalle($codPresupuesto, 99);

        $this->getJson("/api/v1/presupuestos/{$codPresupuesto}", $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.cabecera.estado', 99);
    }

    #[Test]
    public function comprobantesCopiarReturns200(): void
    {
        $codOrigen = $this->uniqueComprobanteCod('PHPCOPY');
        $this->insertComprobanteConDetalle($codOrigen, 0);

        $this->postJson('/api/v1/comprobantes/copiar', [
            'codComprobanteOrigen' => $codOrigen,
            'tipoDestino' => 'presupuesto',
        ], $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.borrador.tipoComprobante', 'presupuesto')
            ->assertJsonPath('resultado.borrador.codComprobanteOrigen', $codOrigen);
    }

    #[Test]
    public function pedidosUpdateReturns200(): void
    {
        $createResponse = $this->postJson('/api/v1/pedidos', $this->sampleGrabacionPayload(), $this->authHeadersFor(self::SUPERVISOR));
        $createResponse->assertOk();
        $codPedido = (string) $createResponse->json('resultado.cod_pedido');

        $updatePayload = $this->sampleGrabacionPayload();
        $updatePayload['cabecera']['observaciones'] = 'Observacion actualizada feature test';

        $this->putJson("/api/v1/pedidos/{$codPedido}", $updatePayload, $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.cod_pedido', $codPedido);
    }

    #[Test]
    public function pedidosDestroyReturns200(): void
    {
        $createResponse = $this->postJson('/api/v1/pedidos', $this->sampleGrabacionPayload(), $this->authHeadersFor(self::SUPERVISOR));
        $createResponse->assertOk();
        $codPedido = (string) $createResponse->json('resultado.cod_pedido');

        $this->deleteJson("/api/v1/pedidos/{$codPedido}", [], $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0);

        $this->getJson("/api/v1/pedidos/{$codPedido}", $this->authHeadersFor(self::SUPERVISOR))
            ->assertNotFound();
    }

    #[Test]
    public function pedidosEdicionFlowReturns200(): void
    {
        $createResponse = $this->postJson('/api/v1/pedidos', $this->sampleGrabacionPayload(), $this->authHeadersFor(self::SUPERVISOR));
        $createResponse->assertOk();
        $codPedido = (string) $createResponse->json('resultado.cod_pedido');
        $headers = $this->authHeadersFor(self::SUPERVISOR);

        $this->postJson("/api/v1/pedidos/{$codPedido}/edicion/iniciar", [], $headers)
            ->assertOk()
            ->assertJsonPath('resultado.estado', -1);

        $this->postJson("/api/v1/pedidos/{$codPedido}/edicion/actividad", [], $headers)
            ->assertOk()
            ->assertJsonStructure(['resultado' => ['fechahora_ultima_actividad']]);

        $this->postJson("/api/v1/pedidos/{$codPedido}/edicion/cancelar", [], $headers)
            ->assertOk()
            ->assertJsonPath('resultado.estado', 0);
    }

    #[Test]
    public function presupuestosUpdateReturns200(): void
    {
        $createResponse = $this->postJson('/api/v1/presupuestos', $this->sampleGrabacionPayload(), $this->authHeadersFor(self::SUPERVISOR));
        $createResponse->assertOk();
        $codPresupuesto = (string) $createResponse->json('resultado.cod_pedido');

        $updatePayload = $this->sampleGrabacionPayload();
        $updatePayload['cabecera']['observaciones'] = 'Presupuesto actualizado feature test';

        $this->putJson("/api/v1/presupuestos/{$codPresupuesto}", $updatePayload, $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 99);
    }

    #[Test]
    public function presupuestosCerrarReturns200(): void
    {
        $codPresupuesto = $this->uniqueComprobanteCod('PRECLOSE');
        $this->insertComprobanteConDetalle($codPresupuesto, 99);

        $this->postJson("/api/v1/presupuestos/{$codPresupuesto}/cerrar", [
            'id_motivo' => $this->motivoRechazoFeatureId(),
            'observacion' => 'Cierre feature test',
        ], $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 98)
            ->assertJsonPath('resultado.cod_presupuesto', $codPresupuesto);
    }

    #[Test]
    public function tratativasIndexAndStoreReturn200(): void
    {
        $codPresupuesto = $this->uniqueComprobanteCod('PRETRAT');
        $this->insertComprobanteConDetalle($codPresupuesto, 99);
        $headers = $this->authHeadersFor(self::SUPERVISOR);

        $this->getJson("/api/v1/presupuestos/{$codPresupuesto}/tratativas", $headers)
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure(['resultado' => ['items']]);

        $this->postJson("/api/v1/presupuestos/{$codPresupuesto}/tratativas", [
            'comentario' => 'Seguimiento feature test',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure(['resultado' => ['id_tratativa']]);
    }
}
