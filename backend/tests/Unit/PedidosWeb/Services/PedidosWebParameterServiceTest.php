<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Services\PedidosWeb\PedidosWebParameterService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidosWebParameterServiceTest extends TestCase
{
    #[Test]
    public function usaDefaultsConfigCuandoLecturaErpEstaDeshabilitada(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.MinutosWeb', 22);
        config()->set('paqsuite_pedidosweb.defaults.Mail_DireccionRemitente', 'erp@paqsuite.local');

        $service = new PedidosWebParameterService();

        $this->assertSame(22, $service->getMinutosWeb());
        $this->assertSame('erp@paqsuite.local', $service->getMailDireccionRemitente());
    }

    #[Test]
    public function resolveModificaFlagsClienteSiempreFalse(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ModificaPrecioV', 1);

        $service = new PedidosWebParameterService();

        $this->assertSame([
            'modificaPrecio' => false,
            'modificaBonArt' => false,
            'modificaBonCli' => false,
            'modificaListaPrec' => false,
            'modificaCondVta' => false,
            'modificaDirEntr' => true,
            'modificaExpreso' => true,
        ], $service->resolveModificaFlags('cliente'));
    }

    #[Test]
    public function resolveModificaFlagsSupervisorUsaSufijoS(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ModificaPrecioS', 0);
        config()->set('paqsuite_pedidosweb.defaults.ModificaBonArtS', 1);
        config()->set('paqsuite_pedidosweb.defaults.ModificaBonCliS', 0);
        config()->set('paqsuite_pedidosweb.defaults.ModificaListaPrecS', 1);
        config()->set('paqsuite_pedidosweb.defaults.ModificaCondVtaS', 0);
        config()->set('paqsuite_pedidosweb.defaults.ModificaDirEntrS', 1);
        config()->set('paqsuite_pedidosweb.defaults.ModificaExpresoS', 0);

        $service = new PedidosWebParameterService();

        $this->assertSame([
            'modificaPrecio' => false,
            'modificaBonArt' => true,
            'modificaBonCli' => false,
            'modificaListaPrec' => true,
            'modificaCondVta' => false,
            'modificaDirEntr' => true,
            'modificaExpreso' => false,
        ], $service->resolveModificaFlags('supervisor'));
    }

    #[Test]
    public function getActualizarPrecioCopiaDefaultFalse(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ActualizarPrecioCopia', 0);

        $service = new PedidosWebParameterService();

        $this->assertFalse($service->getActualizarPrecioCopia());
    }

    #[Test]
    public function getActualizarPrecioCopiaLeeConfigCuandoErpDeshabilitado(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.ActualizarPrecioCopia', 1);

        $service = new PedidosWebParameterService();

        $this->assertTrue($service->getActualizarPrecioCopia());
    }

    #[Test]
    public function getArticulosSinPrecioPrefiereClaveCanonicaSobreLegacy(): void
    {
        config()->set('paqsuite_pedidosweb.readFromErp', true);

        $canonical = new \App\Models\PqParametrosGral();
        $canonical->Clave = 'ArticulosSinPrecio';
        $canonical->tipo_valor = 'B';
        $canonical->Valor_Bool = false;

        $legacy = new \App\Models\PqParametrosGral();
        $legacy->Clave = 'Articulossinprecio';
        $legacy->tipo_valor = 'B';
        $legacy->Valor_Bool = true;

        $service = new PedidosWebParameterService();
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('parametrosPorClave');
        $property->setAccessible(true);
        $property->setValue($service, [
            'ArticulosSinPrecio' => $canonical,
            'Articulossinprecio' => $legacy,
        ]);

        $this->assertFalse($service->getArticulosSinPrecio());
    }
}
