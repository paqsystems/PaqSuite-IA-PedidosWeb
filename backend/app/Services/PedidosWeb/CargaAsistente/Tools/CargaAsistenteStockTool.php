<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Services\PedidosWeb\StockConsultaService;

final class CargaAsistenteStockTool
{
    public function __construct(
        private readonly StockConsultaService $stockConsultaService,
    ) {}

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function consultaStock(string $q): array
    {
        $q = trim($q);
        $result = $this->stockConsultaService->listar([
            'q' => $q !== '' ? $q : null,
            'page' => 1,
            'page_size' => 10,
        ]);

        $total = (int) ($result['total'] ?? 0);

        if ($total > 10) {
            return [
                'replyText' => 'pedidos.carga.asistente.needsRefine',
                'actions' => [
                    [
                        'action' => 'needsRefine',
                        'payload' => [
                            'kind' => 'stock',
                            'hint' => 'pedidos.carga.asistente.needsRefine',
                        ],
                        'resultado' => 'needsRefine',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        /** @var list<array<string, mixed>> $items */
        $items = array_values($result['items'] ?? []);
        $totals = [
            'stock' => 0.0,
            'comprometido' => 0.0,
            'comprometidoWeb' => 0.0,
            'disponibleNeto' => 0.0,
        ];

        foreach ($items as $item) {
            $totals['stock'] += (float) ($item['stock'] ?? 0);
            $totals['comprometido'] += (float) ($item['comprometido'] ?? 0);
            $totals['comprometidoWeb'] += (float) ($item['comprometidoWeb'] ?? 0);
            $totals['disponibleNeto'] += (float) ($item['disponibleNeto'] ?? 0);
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        return [
            'replyText' => $total === 0
                ? 'Sin resultados de stock.'
                : 'Stock encontrado: '.$total.' ítem(s).',
            'actions' => [
                [
                    'action' => 'showConsulta',
                    'payload' => [
                        'kind' => 'stock',
                        'items' => $items,
                        'total' => $total,
                        'totals' => $totals,
                        'columns' => [
                            'codArticulo',
                            'descripcion',
                            'stock',
                            'comprometido',
                            'comprometidoWeb',
                            'disponibleNeto',
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
