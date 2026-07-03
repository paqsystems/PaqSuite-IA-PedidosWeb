<?php

namespace Tests\Unit\Seed;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PqParametrosGralPedidosWebSeedTest extends TestCase
{
    #[Test]
    public function seedIncluyeActualizarPrecioCopiaBooleano(): void
    {
        $seedPath = base_path('../docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json');
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
}
