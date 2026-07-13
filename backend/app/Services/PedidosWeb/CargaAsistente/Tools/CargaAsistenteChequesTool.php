<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\User;
use App\Services\PedidosWeb\CargaAsistente\CargaAsistenteConsultaFormatting;
use App\Services\PedidosWeb\ChequesConsultaService;

final class CargaAsistenteChequesTool
{
    public function __construct(
        private readonly ChequesConsultaService $chequesConsultaService,
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
    public function consultaCheques(User $user, array $draftContext): array
    {
        $codCliente = $draftContext['codCliente'];

        if ($codCliente === null || $codCliente === '') {
            return [
                'replyText' => 'pedidos.carga.asistente.needsCliente',
                'actions' => [
                    [
                        'action' => 'needsRefine',
                        'payload' => [
                            'kind' => 'cheques',
                            'hint' => 'pedidos.carga.asistente.needsCliente',
                        ],
                        'resultado' => 'needsRefine',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        $result = $this->chequesConsultaService->listar($user, [
            'cod_cliente' => $codCliente,
            'page' => 1,
            'page_size' => 10,
        ]);

        $total = (int) ($result['total'] ?? 0);
        $items = [];

        foreach (($result['items'] ?? []) as $row) {
            $importe = round((float) ($row['importe'] ?? 0), 2);
            $items[] = [
                'nro' => (string) ($row['numero'] ?? ''),
                'fecha' => CargaAsistenteConsultaFormatting::formatDateOnly($row['fecha'] ?? null),
                'importe' => $importe,
            ];
        }

        $totals = CargaAsistenteConsultaFormatting::totalsIfMultiple($items, 'importe');

        return [
            'replyText' => $total === 0 ? 'Sin cheques.' : 'Cheques: '.$total.' ítem(s).',
            'actions' => [
                [
                    'action' => 'showConsulta',
                    'payload' => [
                        'kind' => 'cheques',
                        'items' => $items,
                        'total' => $total,
                        'totals' => $totals,
                        'columns' => ['nro', 'fecha', 'importe'],
                    ],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
