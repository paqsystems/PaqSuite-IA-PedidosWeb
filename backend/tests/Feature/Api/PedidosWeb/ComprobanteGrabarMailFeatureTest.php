<?php

namespace Tests\Feature\Api\PedidosWeb;

use App\Mail\ComprobanteNotificationMail;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\TestCase;

final class ComprobanteGrabarMailFeatureTest extends TestCase
{
    use SeedsPedidosWebFeatureData;

    private const SUPERVISOR = 'supervisor.mvp';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPedidosWebFeature();
    }

    #[Test]
    public function grabarPedidoDisparaMailYExponeMailEnviadoTrue(): void
    {
        $response = $this->postJson('/api/v1/comprobantes/grabar', [
            'accionGrabacion' => 'pedido',
            ...$this->sampleGrabacionPayload(),
        ], $this->authHeadersFor(self::SUPERVISOR));

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 0)
            ->assertJsonPath('resultado.mailEnviado', true);

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            return $mail->hasTo('cliente.mvp@paqsuite.local')
                && ($mail->comprobanteViewData['mostrarDetalle'] ?? false) === true
                && count($mail->comprobanteViewData['detalle'] ?? []) >= 1;
        });
    }

    #[Test]
    public function modificarPedidoReenviaMail(): void
    {
        $headers = $this->authHeadersFor(self::SUPERVISOR);
        $createResponse = $this->postJson('/api/v1/pedidos', $this->sampleGrabacionPayload(), $headers);
        $createResponse->assertOk()->assertJsonPath('resultado.mailEnviado', true);

        $codPedido = (string) $createResponse->json('resultado.cod_pedido');
        $updatePayload = $this->sampleGrabacionPayload();
        $updatePayload['cabecera']['observaciones'] = 'Pedido modificado mail test';

        $this->putJson("/api/v1/pedidos/{$codPedido}", $updatePayload, $headers)
            ->assertOk()
            ->assertJsonPath('resultado.mailEnviado', true);

        Mail::assertSent(ComprobanteNotificationMail::class, 2);
    }

    #[Test]
    public function modificarPresupuestoReenviaMail(): void
    {
        $headers = $this->authHeadersFor(self::SUPERVISOR);
        $createResponse = $this->postJson('/api/v1/presupuestos', $this->sampleGrabacionPayload(), $headers);
        $createResponse->assertOk()->assertJsonPath('resultado.estado', 99);

        $codPresupuesto = (string) $createResponse->json('resultado.cod_pedido');
        $updatePayload = $this->sampleGrabacionPayload();
        $updatePayload['cabecera']['observaciones'] = 'Presupuesto modificado mail test';

        $this->putJson("/api/v1/presupuestos/{$codPresupuesto}", $updatePayload, $headers)
            ->assertOk()
            ->assertJsonPath('resultado.mailEnviado', true);

        Mail::assertSent(ComprobanteNotificationMail::class, 2);
    }

    #[Test]
    public function grabarSinDestinatariosPersisteComprobanteConMailEnviadoFalse(): void
    {
        $codCliente = 'CLI-NOMAIL-'.substr(uniqid(), -6);
        $this->ensureClienteSinEmailDestinatarios($codCliente);

        try {
            $response = $this->postJson('/api/v1/pedidos', $this->sampleGrabacionPayload($codCliente), $this->authHeadersFor(self::SUPERVISOR));
        } catch (\Throwable) {
            $this->markTestSkipped('Requiere tabla pq_pedidosweb_logs_integracion (tanda 2 con SQL Server).');
        }

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.estado', 0)
            ->assertJsonPath('resultado.mailEnviado', false);

        Mail::assertNothingSent();
    }
}
