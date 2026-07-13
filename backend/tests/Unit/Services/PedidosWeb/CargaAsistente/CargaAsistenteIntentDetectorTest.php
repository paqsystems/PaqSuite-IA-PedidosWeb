<?php

namespace Tests\Unit\Services\PedidosWeb\CargaAsistente;

use App\Services\PedidosWeb\CargaAsistente\CargaAsistenteIntentDetector;
use PHPUnit\Framework\TestCase;

final class CargaAsistenteIntentDetectorTest extends TestCase
{
    public function testDetectsArticuloWithUnidadesAndPriceWithoutKeyword(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect('almendra carmel 10 unidades 120 $', null);

        $this->assertSame('addRenglon', $detected['intent']);
        $this->assertSame(10.0, $detected['params']['cantidad']);
        $this->assertSame(120.0, $detected['params']['precio']);
        $this->assertStringContainsString('almendra', mb_strtolower((string) $detected['params']['q']));
        $this->assertStringNotContainsString('unidades', mb_strtolower((string) $detected['params']['q']));
    }

    public function testDetectsArticuloWithAgregarKeyword(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect('agregar articulo ABC-01 cantidad 3', null);

        $this->assertSame('addRenglon', $detected['intent']);
        $this->assertSame(3.0, $detected['params']['cantidad']);
    }

    public function testExtractsQuotedDescripcionAndPrecioWithoutEatingWords(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect(
            'articulo "almendra carmel 20/2210" cantidad 10 precio 150',
            null,
        );

        $this->assertSame('addRenglon', $detected['intent']);
        $this->assertSame(10.0, $detected['params']['cantidad']);
        $this->assertSame(150.0, $detected['params']['precio']);
        $this->assertSame('almendra carmel 20/2210', $detected['params']['q']);
    }

    public function testExtractsBonificacionAndDescuentoWithOptionalPercent(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $withBonif = $detector->detect(
            'articulo "aji molido5" cantidad 10 precio 183.50 bonificacion 3%',
            null,
        );
        $this->assertSame(3.0, $withBonif['params']['porcBonif']);

        $withDesc = $detector->detect(
            'articulo ABC cantidad 2 precio 10 descuento 5',
            null,
        );
        $this->assertSame(5.0, $withDesc['params']['porcBonif']);

        $withDto = $detector->detect(
            'articulo XYZ cant 1 bonif 2.5',
            null,
        );
        $this->assertSame(2.5, $withDto['params']['porcBonif']);
    }

    public function testDetectsBonificacionCabeceraSlots(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $bonif1 = $detector->detect('bonificacion 1 5', null);
        $this->assertSame('setCampoLibre', $bonif1['intent']);
        $this->assertSame('bonif1', $bonif1['params']['field']);
        $this->assertSame('5', $bonif1['params']['value']);

        $bonif2 = $detector->detect('bonif 2: 3.5%', null);
        $this->assertSame('bonif2', $bonif2['params']['field']);
        $this->assertStringContainsString('3.5', (string) $bonif2['params']['value']);

        $bonif3 = $detector->detect('bonificación 3 -2', null);
        $this->assertSame('bonif3', $bonif3['params']['field']);
        $this->assertStringContainsString('-2', (string) $bonif3['params']['value']);
    }

    public function testDetectsExpresoAndDireccionExpreso(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $expreso = $detector->detect('expreso Andreani', null);
        $this->assertSame('setCampoLibre', $expreso['intent']);
        $this->assertSame('expreso', $expreso['params']['field']);
        $this->assertSame('Andreani', $expreso['params']['value']);

        $dire = $detector->detect('direccion expreso Calle Falsa 123', null);
        $this->assertSame('expresoDire', $dire['params']['field']);
        $this->assertSame('Calle Falsa 123', $dire['params']['value']);
    }

    public function testDetectsTransporte(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect('transporte Pablo', null);

        $this->assertSame('setTransporte', $detected['intent']);
        $this->assertSame('Pablo', $detected['params']['q']);
    }

    public function testDetectsCabeceraLookupsAndFecha(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $condicion = $detector->detect('condicion de venta contado', null);
        $this->assertSame('setCondicionVenta', $condicion['intent']);
        $this->assertSame('contado', $condicion['params']['q']);

        $perfil = $detector->detect('perfil STANDARD', null);
        $this->assertSame('setPerfil', $perfil['intent']);
        $this->assertSame('STANDARD', $perfil['params']['q']);

        $lista = $detector->detect('lista de precios 2', null);
        $this->assertSame('setListaPrecios', $lista['intent']);
        $this->assertSame('2', $lista['params']['q']);

        $fecha = $detector->detect('fecha de entrega 15/07/2026', null);
        $this->assertSame('setFechaEntrega', $fecha['intent']);
        $this->assertSame('15/07/2026', $fecha['params']['value']);

        $dir = $detector->detect('direccion de entrega Mitre', null);
        $this->assertSame('setDireccionEntrega', $dir['intent']);
        $this->assertSame('Mitre', $dir['params']['q']);
    }

    public function testDetectsRemoveAndUpdateRenglon(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $remove = $detector->detect('eliminar articulo almendra', null);
        $this->assertSame('mutateRenglon', $remove['intent']);
        $this->assertSame('remove', $remove['params']['operation']);
        $this->assertStringContainsString('almendra', mb_strtolower((string) $remove['params']['q']));

        $removeConjugado = $detector->detect('elimina el articulo arroz', null);
        $this->assertSame('mutateRenglon', $removeConjugado['intent']);
        $this->assertSame('remove', $removeConjugado['params']['operation']);
        $this->assertSame('arroz', mb_strtolower((string) $removeConjugado['params']['q']));

        $update = $detector->detect('cambiar cantidad a 5 del articulo ABC', null);
        $this->assertSame('mutateRenglon', $update['intent']);
        $this->assertSame('update', $update['params']['operation']);
        $this->assertSame(5.0, $update['params']['cantidad']);
        $this->assertSame('ABC', $update['params']['q']);

        $updateQuoted = $detector->detect('cambiar cantidad del articulo "almendra tostada" a 150', null);
        $this->assertSame('mutateRenglon', $updateQuoted['intent']);
        $this->assertSame(150.0, $updateQuoted['params']['cantidad']);
        $this->assertSame('almendra tostada', mb_strtolower((string) $updateQuoted['params']['q']));

        $ultimo = $detector->detect('poner precio 1500 en el ultimo renglon', null);
        $this->assertSame('mutateRenglon', $ultimo['intent']);
        $this->assertTrue((bool) $ultimo['params']['ultimo']);
        $this->assertSame(1500.0, $ultimo['params']['precio']);
    }
}
