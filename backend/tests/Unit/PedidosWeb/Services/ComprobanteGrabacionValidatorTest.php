<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Exceptions\PedidosWebBusinessException;
use App\Services\PedidosWeb\ComprobanteGrabacionValidator;
use App\Services\PedidosWeb\PedidosWebParameterService;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ComprobanteGrabacionValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::shouldReceive('hasTable')->andReturn(false);
        Schema::shouldReceive('hasColumn')->andReturn(false);
    }
    #[Test]
    public function acumulaMultiplesErroresDeCabecera(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);

        $validator = new ComprobanteGrabacionValidator(new PedidosWebParameterService());

        $errores = $validator->collectComprobanteGrabableErrors([
            'cod_cliente' => '',
            'cod_vended' => '',
            'cod_perfil' => '',
            'cod_condvta' => 0,
            'cod_transpor' => '',
            'id_de' => 0,
            'lista_precios' => 0,
        ], []);

        $this->assertContains('business.clienteRequerido', $errores);
        $this->assertContains('business.vendedorRequerido', $errores);
        $this->assertContains('business.perfilRequerido', $errores);
        $this->assertContains('business.condicionVentaRequerida', $errores);
        $this->assertContains('business.transporteRequerido', $errores);
        $this->assertContains('business.direccionEntregaRequerida', $errores);
        $this->assertContains('business.listaPreciosRequerida', $errores);
        $this->assertContains('business.sinRenglones', $errores);
    }

    #[Test]
    public function rechazaComprobanteSinRenglones(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);

        $validator = new ComprobanteGrabacionValidator(new PedidosWebParameterService());

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.sinRenglones');

        $validator->assertComprobanteGrabable($this->validCabecera(), []);
    }

    #[Test]
    public function rechazaNivelInvalidoCuandoNivelExtremoActivo(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.NivelExtremo', 1);

        $validator = new ComprobanteGrabacionValidator(new PedidosWebParameterService());

        $cabecera = $this->validCabecera();
        $cabecera['nivel'] = 50;

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.nivelExtremoInvalido');

        $validator->assertComprobanteGrabable($cabecera, $this->validRenglones());
    }

    #[Test]
    public function rechazaPrecioCeroCuandoParametroLoProhibe(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ArticulosPrecioCero', 0);
        config()->set('paqsuite_pedidosweb.defaults.ArticulosSinPrecio', 0);

        $validator = new ComprobanteGrabacionValidator(new PedidosWebParameterService());

        $this->expectException(PedidosWebBusinessException::class);
        $this->expectExceptionMessage('business.precioCeroNoPermitido');

        $validator->assertComprobanteGrabable($this->validCabecera(), [[
            'cod_articulo' => 'ART001',
            'cantidad' => 1,
            'precio' => 0,
        ]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validCabecera(): array
    {
        return [
            'cod_cliente' => 'CLI001',
            'cod_vended' => 'V001',
            'cod_perfil' => 'MVP',
            'cod_condvta' => 1,
            'cod_transpor' => 'T001',
            'id_de' => 1,
            'lista_precios' => 1,
            'nivel' => 0,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function validRenglones(): array
    {
        return [[
            'cod_articulo' => 'ART001',
            'cantidad' => 1,
            'precio' => 10,
        ]];
    }
}
