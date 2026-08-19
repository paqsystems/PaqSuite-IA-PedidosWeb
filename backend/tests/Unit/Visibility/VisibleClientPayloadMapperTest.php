<?php

declare(strict_types=1);

namespace Tests\Unit\Visibility;

use App\Services\Visibility\VisibleClientPayloadMapper;
use PHPUnit\Framework\TestCase;

final class VisibleClientPayloadMapperTest extends TestCase
{
    public function testMapClienteIncludesEmptyContactos(): void
    {
        $cliente = (object) [
            'cod_client' => 'CLI01',
            'nombre' => 'Acme',
            'razon_soci' => 'Acme SA',
            'fantasia' => 'Acme',
            'cod_vended' => 'VEN01',
            'e_mail' => 'acme@test.local',
        ];

        $payload = VisibleClientPayloadMapper::mapCliente($cliente, []);

        $this->assertSame('CLI01', $payload['codCliente']);
        $this->assertSame('Acme SA', $payload['razonSocial']);
        $this->assertSame([], $payload['contactos']);
    }

    public function testMapContactoUsesCamelCase(): void
    {
        $contacto = (object) [
            'id' => 7,
            'cod_contacto' => 'C01',
            'nombre' => 'Ana',
            'telefono' => '111',
            'mail' => 'ana@test.local',
        ];

        $this->assertSame(
            [
                'id' => 7,
                'codContacto' => 'C01',
                'nombre' => 'Ana',
                'telefono' => '111',
                'mail' => 'ana@test.local',
            ],
            VisibleClientPayloadMapper::mapContacto($contacto)
        );
    }

    public function testMapContactoNormalizesEmptyTelefonoAndMailToNull(): void
    {
        $contacto = (object) [
            'id' => 1,
            'cod_contacto' => 'C02',
            'nombre' => 'Beta',
            'telefono' => '',
            'mail' => null,
        ];

        $payload = VisibleClientPayloadMapper::mapContacto($contacto);

        $this->assertNull($payload['telefono']);
        $this->assertNull($payload['mail']);
    }
}
