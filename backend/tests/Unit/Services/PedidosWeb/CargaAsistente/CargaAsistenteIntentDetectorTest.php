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

    public function testDetectsDesctoCabeceraSlotAndBareDireccionAsExpresoDire(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $descto = $detector->detect('Descto 3: 4', null);
        $this->assertSame('setCampoLibre', $descto['intent']);
        $this->assertSame('bonif3', $descto['params']['field']);
        $this->assertSame('4', $descto['params']['value']);

        $direccion = $detector->detect('Direccion: san martin 2470', null);
        $this->assertSame('setCampoLibre', $direccion['intent']);
        $this->assertSame('expresoDire', $direccion['params']['field']);
        $this->assertSame('san martin 2470', $direccion['params']['value']);
    }

    public function testDetectsArticuloWithCantiAlias(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect('articulo "AJO EN POLVO25 kg" canti: 100', null);

        $this->assertSame('addRenglon', $detected['intent']);
        $this->assertSame(100.0, $detected['params']['cantidad']);
        $this->assertSame('AJO EN POLVO25 kg', $detected['params']['q']);
    }

    public function testDetectsArticuloWithAbbreviationsItemAndIt(): void
    {
        $detector = new CargaAsistenteIntentDetector();

        $art = $detector->detect('art. "AJO EN POLVO25 kg" canti: 100', null);
        $this->assertSame('addRenglon', $art['intent']);
        $this->assertSame(100.0, $art['params']['cantidad']);
        $this->assertSame('AJO EN POLVO25 kg', $art['params']['q']);

        $artSinPunto = $detector->detect('art almendra cant: 12', null);
        $this->assertSame('addRenglon', $artSinPunto['intent']);
        $this->assertSame(12.0, $artSinPunto['params']['cantidad']);
        $this->assertStringContainsString('almendra', mb_strtolower((string) $artSinPunto['params']['q']));

        $item = $detector->detect('item arroz largo fino 5/05 cant: 10 precio: 150', null);
        $this->assertSame('addRenglon', $item['intent']);
        $this->assertSame(10.0, $item['params']['cantidad']);
        $this->assertSame(150.0, $item['params']['precio']);
        $this->assertStringContainsString('arroz', mb_strtolower((string) $item['params']['q']));
        $this->assertStringNotContainsString('item', mb_strtolower((string) $item['params']['q']));

        $it = $detector->detect('it "almendra ramillada10 kg" cant: 120', null);
        $this->assertSame('addRenglon', $it['intent']);
        $this->assertSame(120.0, $it['params']['cantidad']);
        $this->assertSame('almendra ramillada10 kg', $it['params']['q']);
    }

    public function testDetectsCompositePedidoWithAllCabeceraFields(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $message = <<<'TXT'
Cliente: 101093
Perfil: aukanes presupuesto
condición de venta: 180
fecha de entrega: 31-07-2026
transporte: retira por deposito
Expreso: la estrella
Direccion: san martin 2470
Lista de Precios: Ankas C
Bonificación 1: 3%
Bonif 2: 5
Descto 3: 4
Leyenda 1: entregar lista de precios
Observaciones: tener en cuenta horario de atención
articulo "AJO EN POLVO25 kg" canti: 100
articulo almendra ramillada10 kg cant: 120
articulo arroz largo fino 5/05 cant: 10 precio: 150 bonif: 3
TXT;

        $detected = $detector->detect($message, null);

        $this->assertSame('compositePedido', $detected['intent']);
        $items = $detected['params']['items'];
        $this->assertIsArray($items);
        $this->assertGreaterThanOrEqual(14, count($items));

        $intents = array_column($items, 'intent');
        $this->assertContains('selectCliente', $intents);
        $this->assertContains('setPerfil', $intents);
        $this->assertContains('setCondicionVenta', $intents);
        $this->assertContains('setFechaEntrega', $intents);
        $this->assertContains('setTransporte', $intents);
        $this->assertContains('setListaPrecios', $intents);
        $this->assertContains('setCampoLibre', $intents);
        $this->assertContains('addRenglon', $intents);

        $cliente = $items[0];
        $this->assertSame('selectCliente', $cliente['intent']);
        $this->assertSame('101093', $cliente['params']['q']);

        $fields = [];
        foreach ($items as $item) {
            if (($item['intent'] ?? '') === 'setCampoLibre') {
                $fields[(string) ($item['params']['field'] ?? '')] = (string) ($item['params']['value'] ?? '');
            }
        }

        $this->assertSame('la estrella', $fields['expreso'] ?? null);
        $this->assertSame('san martin 2470', $fields['expresoDire'] ?? null);
        $this->assertSame('3%', $fields['bonif1'] ?? null);
        $this->assertSame('5', $fields['bonif2'] ?? null);
        $this->assertSame('4', $fields['bonif3'] ?? null);
        $this->assertSame('entregar lista de precios', $fields['leyenda1'] ?? null);
        $this->assertStringContainsString('horario', $fields['observaciones'] ?? '');

        $addRenglones = array_values(array_filter(
            $items,
            static fn (array $item): bool => ($item['intent'] ?? '') === 'addRenglon',
        ));
        $this->assertCount(3, $addRenglones);
        $this->assertSame(100.0, $addRenglones[0]['params']['cantidad']);
        $this->assertSame(120.0, $addRenglones[1]['params']['cantidad']);
        $this->assertSame(10.0, $addRenglones[2]['params']['cantidad']);
        $this->assertSame(150.0, $addRenglones[2]['params']['precio']);
        $this->assertSame(3.0, $addRenglones[2]['params']['porcBonif']);
    }

    public function testDetectsCompositePedidoOnSingleLineDictation(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $message = 'cliente bernascone artículo ajo en polvo 25 kg cantidad 100 '
            .'artículo almendra ramillada 10 cantidad 120 '
            .'artículo arroz largo fino 5/05 cantidad 10 precio 150 bonificación 3';

        $detected = $detector->detect($message, null);

        $this->assertSame('compositePedido', $detected['intent']);
        $items = $detected['params']['items'];
        $this->assertIsArray($items);
        $this->assertCount(4, $items);

        $this->assertSame('selectCliente', $items[0]['intent']);
        $this->assertSame('bernascone', $items[0]['params']['q']);

        $addRenglones = array_values(array_filter(
            $items,
            static fn (array $item): bool => ($item['intent'] ?? '') === 'addRenglon',
        ));
        $this->assertCount(3, $addRenglones);
        $this->assertSame(100.0, $addRenglones[0]['params']['cantidad']);
        $this->assertStringContainsString('ajo', mb_strtolower((string) $addRenglones[0]['params']['q']));
        $this->assertStringNotContainsString('cantidad', mb_strtolower((string) $addRenglones[0]['params']['q']));
        $this->assertSame(120.0, $addRenglones[1]['params']['cantidad']);
        $this->assertSame(10.0, $addRenglones[2]['params']['cantidad']);
        $this->assertSame(150.0, $addRenglones[2]['params']['precio']);
        $this->assertSame(3.0, $addRenglones[2]['params']['porcBonif']);
    }

    public function testDetectsClienteThenArticuloOnSingleLineWithoutEatingArticulo(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect('cliente agromenta artículo ajo en polvo 25 cantidad 100', null);

        $this->assertSame('compositePedido', $detected['intent']);
        $this->assertSame('selectCliente', $detected['params']['items'][0]['intent']);
        $this->assertSame('agromenta', $detected['params']['items'][0]['params']['q']);
        $this->assertSame('addRenglon', $detected['params']['items'][1]['intent']);
        $this->assertSame(100.0, $detected['params']['items'][1]['params']['cantidad']);
        $this->assertStringContainsString('ajo', mb_strtolower((string) $detected['params']['items'][1]['params']['q']));
    }

    public function testDetectsClienteWithColonWithoutEatingNextFields(): void
    {
        $detector = new CargaAsistenteIntentDetector();
        $detected = $detector->detect("Cliente: 101093\nPerfil: dummy", null);

        // Multiline with 2 labels → composite; client q must stay clean.
        $this->assertSame('compositePedido', $detected['intent']);
        $this->assertSame('101093', $detected['params']['items'][0]['params']['q']);
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
