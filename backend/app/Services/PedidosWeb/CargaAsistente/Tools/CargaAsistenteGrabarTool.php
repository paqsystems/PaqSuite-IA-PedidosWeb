<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

final class CargaAsistenteGrabarTool
{
    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function grabarPedido(): array
    {
        return [
            'replyText' => 'Grabando pedido…',
            'actions' => [
                [
                    'action' => 'grabarPedido',
                    'payload' => ['invokeLocalGrabar' => true],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function grabarPresupuesto(): array
    {
        return [
            'replyText' => 'Grabando presupuesto…',
            'actions' => [
                [
                    'action' => 'grabarPresupuesto',
                    'payload' => ['invokeLocalGrabar' => true],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
