<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\User;
use App\Services\PedidosWeb\CargaAsistente\CargaAsistenteConsultaFormatting;
use App\Services\PedidosWeb\DeudaConsultaService;

final class CargaAsistenteDeudaTool
{
    public function __construct(
        private readonly DeudaConsultaService $deudaConsultaService,
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
    public function consultaDeuda(User $user, array $draftContext): array
    {
        $codCliente = $draftContext['codCliente'];

        if ($codCliente === null || $codCliente === '') {
            return [
                'replyText' => 'pedidos.carga.asistente.needsCliente',
                'actions' => [
                    [
                        'action' => 'needsRefine',
                        'payload' => [
                            'kind' => 'deuda',
                            'hint' => 'pedidos.carga.asistente.needsCliente',
                        ],
                        'resultado' => 'needsRefine',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        $result = $this->deudaConsultaService->listar($user, [
            'cod_cliente' => $codCliente,
            'page' => 1,
            'page_size' => 10,
        ]);

        $total = (int) ($result['total'] ?? 0);
        $items = [];

        foreach (($result['items'] ?? []) as $row) {
            $saldo = round((float) ($row['saldo'] ?? 0), 2);
            $items[] = [
                'tipoNro' => trim((string) (($row['tipo'] ?? '').' '.($row['numero'] ?? ''))),
                'fecha' => CargaAsistenteConsultaFormatting::formatDateOnly($row['fecha'] ?? null),
                'vencimiento' => CargaAsistenteConsultaFormatting::formatDateOnly($row['vencimiento'] ?? null),
                'saldo' => $saldo,
            ];
        }

        $totals = CargaAsistenteConsultaFormatting::totalsIfMultiple($items, 'saldo');

        return [
            'replyText' => $total === 0 ? 'Sin deuda.' : 'Deuda: '.$total.' comprobante(s).',
            'actions' => [
                [
                    'action' => 'showConsulta',
                    'payload' => [
                        'kind' => 'deuda',
                        'items' => $items,
                        'total' => $total,
                        'totals' => $totals,
                        'columns' => ['tipoNro', 'fecha', 'vencimiento', 'saldo'],
                    ],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
