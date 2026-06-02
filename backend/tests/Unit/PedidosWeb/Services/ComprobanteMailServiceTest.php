<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Mail\ComprobanteNotificationMail;
use App\Models\PqPedidoswebCliente;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\PqPedidoswebVendedor;
use App\Models\User;
use App\Services\PedidosWeb\ComprobanteMailService;
use App\Services\PedidosWeb\LogIntegracionService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ComprobanteMailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.MailDestinatariosAdicionales', '');
        config()->set('paqsuite_pedidosweb.defaults.mailCCO', '');
        config()->set('paqsuite_pedidosweb.defaults.DetallePorMail', 1);
        config()->set('paqsuite_pedidosweb.defaults.Mail_DireccionRemitente', 'pedidos@empresa.test');
        config()->set('paqsuite_pedidosweb.defaults.MonedaSimbolo', '$');
    }

    #[Test]
    public function enviaMailCuandoHayDestinatarioValido(): void
    {
        Mail::fake();

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';
        $cliente->nombre = 'Cliente MVP';

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $service = $this->buildService();

        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante(
            $cabecera,
            [
                [
                    'cod_articulo' => 'ART1',
                    'descripcion_articulo' => 'Artículo',
                    'cantidad' => 1,
                    'precio' => 100,
                    'porc_bonif' => 0,
                    'precio_neto' => 100,
                ],
            ],
            'pedido',
            'ingresado',
            $user
        ));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            return $mail->hasTo('cliente@empresa.test');
        });
    }

    #[Test]
    public function deduplicaDestinatariosSinDistinguirMayusculas(): void
    {
        Mail::fake();
        config()->set('paqsuite_pedidosweb.defaults.MailDestinatariosAdicionales', 'Cliente@Empresa.TEST');

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';

        $vendedor = new PqPedidoswebVendedor();
        $vendedor->e_mail = 'VENDEDOR@empresa.test';
        $vendedor->mail_supervisor = 'vendedor@empresa.test';

        $cliente->setRelation('vendedor', $vendedor);

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $service = $this->buildService();

        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante($cabecera, [], 'presupuesto', 'modificado', $user));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            $to = collect($mail->to)->pluck('address')->map(fn (string $addr): string => strtolower($addr))->unique();

            return $to->count() === 2
                && $to->contains('cliente@empresa.test')
                && $to->contains('vendedor@empresa.test');
        });
    }

    #[Test]
    public function retornaFalseSinDestinatariosValidos(): void
    {
        Mail::fake();

        $cabecera = $this->buildCabeceraConCliente(null);
        $service = $this->buildService();

        $user = new User();
        $user->locale = 'es';

        try {
            $result = $service->enviarComprobante($cabecera, [], 'pedido', 'ingresado', $user);
        } catch (\Throwable) {
            $this->markTestSkipped('Requiere tabla pq_pedidosweb_logs_integracion (tanda 2 con SQL Server).');
        }

        $this->assertFalse($result);
        Mail::assertNothingSent();
    }

    private function buildService(): ComprobanteMailService
    {
        return new ComprobanteMailService(
            new PedidosWebParameterService(),
            new LogIntegracionService()
        );
    }

    private function buildCabeceraConCliente(?PqPedidoswebCliente $cliente): PqPedidoswebPedidoCabecera
    {
        $cabecera = new PqPedidoswebPedidoCabecera();
        $cabecera->cod_pedido = 'PED-MAIL1';
        $cabecera->cod_cliente = 'CLI001';
        $cabecera->fecha = now();
        $cabecera->total = 100;
        $cabecera->total_iva = 21;
        $cabecera->descuento = 0;
        $cabecera->observaciones = '';
        $cabecera->setRelation('cliente', $cliente);

        return $cabecera;
    }
}
