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
    public function enviaBccDesdeMailCcoConListaSucia(): void
    {
        Mail::fake();
        config()->set(
            'paqsuite_pedidosweb.defaults.mailCCO',
            "cco1@empresa.test,\r\ncco2@empresa.test;\tinvalido,CCO1@empresa.test"
        );

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';
        $cliente->nombre = 'Cliente MVP';

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $service = $this->buildService();
        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante($cabecera, [], 'pedido', 'ingresado', $user));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            $bcc = collect($mail->bcc)->pluck('address')->map(fn (string $addr): string => strtolower($addr))->unique()->values();

            return $mail->hasTo('cliente@empresa.test')
                && $bcc->count() === 2
                && $bcc->contains('cco1@empresa.test')
                && $bcc->contains('cco2@empresa.test');
        });
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
    public function asuntoUsaLocaleDelUsuarioAunqueAppLocaleSeaIngles(): void
    {
        Mail::fake();
        app()->setLocale('en');

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';
        $cliente->nombre = 'Cliente MVP';

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $service = $this->buildService();

        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante($cabecera, [], 'pedido', 'ingresado', $user));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            if ($mail->mailLocale !== 'es') {
                return false;
            }

            $subject = (new ComprobanteNotificationMail(
                $mail->comprobanteViewData,
                $mail->fromAddress,
                $mail->fromName,
                $mail->mailLocale,
            ))->build()->subject;

            return str_contains($subject, 'Pedido ingresado')
                && ! str_contains($subject, 'Order entered');
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
    public function importesCabeceraReflejanDescuentosAplicados(): void
    {
        Mail::fake();

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';
        $cliente->nombre = 'Cliente MVP';

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $cabecera->total = 81.0;
        $cabecera->total_iva = 17.01;
        $cabecera->descuento = 10.0;

        $service = $this->buildService();
        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante(
            $cabecera,
            [
                [
                    'cod_articulo' => 'ART1',
                    'descripcion_articulo' => 'Artículo bonificado',
                    'cantidad' => 1,
                    'precio' => 100,
                    'porc_bonif' => 10,
                    'precio_neto' => 81,
                    'importe_neto' => 81,
                ],
            ],
            'pedido',
            'ingresado',
            $user
        ));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            $cabeceraMail = $mail->comprobanteViewData['cabeceraMail'] ?? [];

            return ($cabeceraMail['importeBruto'] ?? '') === '$ 81,00'
                && ($cabeceraMail['importeNeto'] ?? '') === '$ 98,01';
        });
    }

    #[Test]
    public function importeBrutoCabeceraSumaImporteNetoDeRenglones(): void
    {
        Mail::fake();

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';
        $cliente->nombre = 'Cliente MVP';

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $cabecera->total = 150489.36;
        $cabecera->total_iva = 31502.77;
        $cabecera->descuento = 5.5;

        $service = $this->buildService();
        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante(
            $cabecera,
            [
                [
                    'cod_articulo' => 'AC16 1000',
                    'descripcion_articulo' => 'ALMENDRA TIPO NP 20/22',
                    'cantidad' => 10,
                    'precio' => 19906,
                    'porc_bonif' => 20,
                    'precio_neto' => 15048.94,
                    'importe_neto' => 150489.36,
                ],
            ],
            'pedido',
            'ingresado',
            $user
        ));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            $cabeceraMail = $mail->comprobanteViewData['cabeceraMail'] ?? [];

            return ($cabeceraMail['importeBruto'] ?? '') === '$ 150.489,36';
        });
    }

    #[Test]
    public function fechaCabeceraMailUsaFormatoI18nDelUsuario(): void
    {
        Mail::fake();

        $cliente = new PqPedidoswebCliente();
        $cliente->e_mail = 'cliente@empresa.test';
        $cliente->nombre = 'Cliente MVP';

        $cabecera = $this->buildCabeceraConCliente($cliente);
        $cabecera->fecha = \Illuminate\Support\Carbon::parse('2026-08-30');
        $service = $this->buildService();

        $user = new User();
        $user->locale = 'es';

        $this->assertTrue($service->enviarComprobante($cabecera, [], 'pedido', 'ingresado', $user));

        Mail::assertSent(ComprobanteNotificationMail::class, function (ComprobanteNotificationMail $mail): bool {
            $fecha = $mail->comprobanteViewData['cabeceraMail']['fecha'] ?? '';

            return $fecha === '30/08/2026';
        });
    }

    #[Test]
    public function detalleMailConservaCantidadYBultosUnidadesSeparados(): void
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
                    'cod_articulo' => 'ART-DUAL',
                    'descripcion_articulo' => 'Artículo dual',
                    'cantidad' => 2,
                    'cantidad_venta' => 24,
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
            $detalle = $mail->comprobanteViewData['detalle'] ?? [];

            if (! is_array($detalle) || $detalle === []) {
                return false;
            }

            $renglon = $detalle[0];

            return (float) ($renglon['cantidad'] ?? 0) === 2.0
                && (float) ($renglon['cantidad_venta'] ?? 0) === 24.0;
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
