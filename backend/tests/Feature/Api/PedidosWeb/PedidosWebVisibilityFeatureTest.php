<?php

namespace Tests\Feature\Api\PedidosWeb;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\TestCase;

final class PedidosWebVisibilityFeatureTest extends TestCase
{
    use SeedsPedidosWebFeatureData;

    private const VENDEDOR_ACOTADO = 'vendedor.acotado.mvp';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPedidosWebFeature();
        $this->seedVisibilityUniverse();
    }

    #[Test]
    public function pedidosShowOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/pedidos/PED-VEN-B-0', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function grabarPedidoWithClienteOutsideVisibleUniverseReturns404(): void
    {
        $payload = $this->sampleGrabacionPayload('CLI-VEN-B');

        $this->postJson('/api/v1/pedidos', $payload, $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function copiarComprobanteOutsideVisibleUniverseReturns404(): void
    {
        $this->postJson('/api/v1/comprobantes/copiar', [
            'codComprobanteOrigen' => 'PED-VEN-B-0',
            'tipoDestino' => 'pedido',
        ], $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function eliminarPedidoOutsideVisibleUniverseReturns404(): void
    {
        $this->deleteJson('/api/v1/pedidos/PED-VEN-B-0', [], $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function presupuestoCerrarOutsideVisibleUniverseReturns404(): void
    {
        $this->insertComprobanteConDetalle('PRE-VEN-B-99', 99, 'CLI-VEN-B');

        $this->postJson('/api/v1/presupuestos/PRE-VEN-B-99/cerrar', [
            'id_motivo' => 9001,
        ], $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function tratativasOutsideVisibleUniverseReturns404(): void
    {
        $this->insertComprobanteConDetalle('PRE-VEN-B-TRAT', 99, 'CLI-VEN-B');

        $this->getJson('/api/v1/presupuestos/PRE-VEN-B-TRAT/tratativas', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function consultasDeudaWithCodClienteOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/consultas/deuda?cod_cliente=CLI-VEN-B', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function consultasChequesWithCodClienteOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/consultas/cheques?cod_cliente=CLI-VEN-B', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function consultasHistorialWithCodClienteOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/consultas/historial-ventas?cod_cliente=CLI-VEN-B', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function consultasPedidosIngresadosWithCodClienteOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/consultas/pedidos-ingresados?cod_cliente=CLI-VEN-B', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }

    #[Test]
    public function consultasPresupuestosWithCodClienteOutsideVisibleUniverseReturns404(): void
    {
        $this->getJson('/api/v1/consultas/presupuestos?estado=99&cod_cliente=CLI-VEN-B', $this->authHeadersFor(self::VENDEDOR_ACOTADO))
            ->assertNotFound()
            ->assertJsonPath('respuesta', 'resource.notFound');
    }
}
