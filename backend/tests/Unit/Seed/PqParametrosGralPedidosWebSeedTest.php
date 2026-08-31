<?php

namespace Tests\Unit\Seed;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PqParametrosGralPedidosWebSeedTest extends TestCase
{
    private function seedPath(): string
    {
        return dirname(__DIR__, 3).'/../docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json';
    }

    #[Test]
    public function seedIncluyeActualizarPrecioCopiaBooleano(): void
    {
        $seedPath = $this->seedPath();
        $this->assertFileExists($seedPath);

        $entries = json_decode((string) file_get_contents($seedPath), true, 512, JSON_THROW_ON_ERROR);
        $actualizarPrecioCopia = null;

        foreach ($entries as $entry) {
            if (($entry['clave'] ?? '') === 'ActualizarPrecioCopia') {
                $actualizarPrecioCopia = $entry;
                break;
            }
        }

        $this->assertIsArray($actualizarPrecioCopia);
        $this->assertSame('PedidosWeb', $actualizarPrecioCopia['programa']);
        $this->assertSame('B', $actualizarPrecioCopia['tipoValor']);
        $this->assertFalse($actualizarPrecioCopia['valorBool']);
        $this->assertNotSame('', trim((string) ($actualizarPrecioCopia['caption'] ?? '')));
        $this->assertNotSame('', trim((string) ($actualizarPrecioCopia['tooltip'] ?? '')));
    }

    #[Test]
    public function seedIncluyeIncluyeArticulosNoStockeablesBooleano(): void
    {
        $seedPath = $this->seedPath();
        $this->assertFileExists($seedPath);

        $entries = json_decode((string) file_get_contents($seedPath), true, 512, JSON_THROW_ON_ERROR);
        $parametro = null;

        foreach ($entries as $entry) {
            if (($entry['clave'] ?? '') === 'IncluyeArticulosNoStockeables') {
                $parametro = $entry;
                break;
            }
        }

        $this->assertIsArray($parametro);
        $this->assertSame('PedidosWeb', $parametro['programa']);
        $this->assertSame('B', $parametro['tipoValor']);
        $this->assertFalse($parametro['valorBool']);
        $this->assertNotSame('', trim((string) ($parametro['caption'] ?? '')));
        $this->assertNotSame('', trim((string) ($parametro['tooltip'] ?? '')));
    }

    #[Test]
    public function seedIncluyeCargaUnidadesVentaBooleano(): void
    {
        $seedPath = $this->seedPath();
        $this->assertFileExists($seedPath);

        $entries = json_decode((string) file_get_contents($seedPath), true, 512, JSON_THROW_ON_ERROR);
        $cargaUnidadesVenta = null;

        foreach ($entries as $entry) {
            if (($entry['clave'] ?? '') === 'CargaUnidadesVenta') {
                $cargaUnidadesVenta = $entry;
                break;
            }
        }

        $this->assertIsArray($cargaUnidadesVenta);
        $this->assertSame('PedidosWeb', $cargaUnidadesVenta['programa']);
        $this->assertSame('B', $cargaUnidadesVenta['tipoValor']);
        $this->assertFalse($cargaUnidadesVenta['valorBool']);
        $this->assertNotSame('', trim((string) ($cargaUnidadesVenta['caption'] ?? '')));
        $this->assertNotSame('', trim((string) ($cargaUnidadesVenta['tooltip'] ?? '')));
    }
}
