<?php

namespace Tests\Feature\Api\PedidosWeb;

use Tests\TestCase;

final class ComprobanteGrabarTest extends TestCase
{
    public function testComprobanteGrabarRequiresAuthentication(): void
    {
        $payload = [
            'accionGrabacion' => 'pedido',
            'cabecera' => [
                'cod_cliente' => 'CLIMVP001',
            ],
            'renglones' => [
                [
                    'cod_articulo' => 'ART001',
                    'cantidad' => 1,
                    'precio' => 100,
                    'porc_bonif' => 0,
                    'porc_iva' => 21,
                ],
            ],
        ];

        $this->postJson('/api/v1/comprobantes/grabar', $payload, [
            'X-Paq-Cliente' => 'desarrollo',
        ])->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }
}
