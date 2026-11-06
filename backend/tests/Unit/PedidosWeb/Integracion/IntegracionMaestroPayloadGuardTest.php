<?php

namespace Tests\Unit\PedidosWeb\Integracion;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroPayloadGuard;
use PHPUnit\Framework\TestCase;

final class IntegracionMaestroPayloadGuardTest extends TestCase
{
    public function testDetectaDropEnStringAnidado(): void
    {
        $guard = new IntegracionMaestroPayloadGuard();

        $this->assertTrue($guard->containsProhibitedSqlFragment([
            ['descripcion' => 'texto DROP TABLE x'],
        ]));

        $this->assertFalse($guard->containsProhibitedSqlFragment([
            ['descripcion' => 'producto normal'],
        ]));
    }
}
