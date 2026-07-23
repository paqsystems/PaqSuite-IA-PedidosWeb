<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\User;
use App\Services\PedidosWeb\HistorialVentasConsultaService;

final class CargaAsistenteHistorialTool
{
    public function __construct(
        private readonly HistorialVentasConsultaService $historialVentasConsultaService,
    ) {}

    /**
     * @param  array{
     *     modo: string|null,
     *     perfilUsuario: string|null,
     *     codCliente: string|null,
     *     cabecera: array<string, mixed>,
     *     renglones: list<array<string, mixed>>,
     *     readOnly: bool,
     *     codLista: int
     * }  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function consultaHistorial(User $user, array $draftContext): array
    {
        $codCliente = $draftContext['codCliente'];

        if ($codCliente === null || $codCliente === '') {
            return [
                'replyText' => 'pedidos.carga.asistente.needsCliente',
                'actions' => [
                    [
                        'action' => 'needsRefine',
                        'payload' => [
                            'kind' => 'historial',
                            'hint' => 'pedidos.carga.asistente.needsCliente',
                        ],
                        'resultado' => 'needsRefine',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        $result = $this->historialVentasConsultaService->listar($user, [
            'cod_cliente' => $codCliente,
            'page' => 1,
            'page_size' => 10,
        ]);

        $total = (int) ($result['total'] ?? 0);
        $items = [];

        foreach (($result['items'] ?? []) as $row) {
            $items[] = [
                'descripcionArticulo' => (string) ($row['descripcion'] ?? ''),
                'cantidad' => $row['cantidad'] ?? 0,
                'precioUnitarioNeto' => $row['precio'] ?? 0,
                'importe' => $row['totSinImp'] ?? 0,
            ];
        }

        return [
            'replyText' => $total === 0 ? 'Sin historial de ventas.' : 'Historial: '.$total.' ítem(s).',
            'actions' => [
                [
                    'action' => 'showConsulta',
                    'payload' => [
                        'kind' => 'historial',
                        'items' => $items,
                        'total' => $total,
                        'totals' => [],
                        'columns' => [
                            'descripcionArticulo',
                            'cantidad',
                            'precioUnitarioNeto',
                            'importe',
                        ],
                    ],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
