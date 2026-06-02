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
        yield 'motivos cierre' => ['/api/v1/motivos-cierre'];
        yield 'integracion logs' => ['/api/v1/integracion/logs'];
        yield 'dashboard operativo' => ['/api/v1/dashboard/operativo'];
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
        $codPedido = 'PED-HP-SHOW-'.substr(uniqid(), -8);
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
        $codPresupuesto = 'PRE-HP-SHOW-'.substr(uniqid(), -8);
        $this->insertComprobanteConDetalle($codPresupuesto, 99);

        $this->getJson("/api/v1/presupuestos/{$codPresupuesto}", $this->authHeadersFor(self::SUPERVISOR))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.cabecera.estado', 99);
    }

    #[Test]
    public function comprobantesCopiarReturns200(): void
    {
        $codOrigen = 'PED-HP-COPY-'.substr(uniqid(), -8);
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
        $codPresupuesto = 'PRE-HP-CLOSE-'.substr(uniqid(), -8);
        $this->insertComprobanteConDetalle($codPresupuesto, 99);

        $this->postJson("/api/v1/presupuestos/{$codPresupuesto}/cerrar", [
            'id_motivo' => 9001,
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
        $codPresupuesto = 'PRE-HP-TRAT-'.substr(uniqid(), -8);
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
