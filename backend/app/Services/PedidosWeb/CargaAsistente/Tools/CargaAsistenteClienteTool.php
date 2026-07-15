<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\User;
use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\Visibility\VisibleClientsResolver;
use Illuminate\Support\Facades\Schema;

final class CargaAsistenteClienteTool
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly CabeceraInicialService $cabeceraInicialService,
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
    public function selectCliente(User $user, array $draftContext, string $q, bool $forceChange): array
    {
        $q = trim($q);

        if ($q === '') {
            return $this->refineResult('cliente');
        }

        $matches = $this->searchClientes($user, $q, 11);

        // Código exacto gana siempre (LIKE '%10112%' puede devolver >10 y tapar el match único).
        $exactMatches = array_values(array_filter(
            $matches,
            static fn (array $cliente): bool => mb_strtolower(trim($cliente['codCliente'])) === mb_strtolower($q),
        ));
        if ($exactMatches !== []) {
            $matches = $exactMatches;
        }

        $count = count($matches);

        if ($count === 0) {
            return [
                'replyText' => 'pedidos.carga.asistente.clienteNoEncontrado',
                'actions' => [
                    [
                        'action' => 'needsRefine',
                        'payload' => [
                            'kind' => 'cliente',
                            'q' => $q,
                            'hint' => 'pedidos.carga.asistente.clienteNoEncontrado',
                        ],
                        'resultado' => 'needsRefine',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        if ($count > 10) {
            return $this->refineResult('cliente');
        }

        if ($count > 1) {
            $options = [];
            foreach ($matches as $index => $cliente) {
                $options[] = [
                    'n' => $index + 1,
                    'label' => trim($cliente['codCliente'].' — '.$cliente['razonSocial']),
                    'code' => $cliente['codCliente'],
                ];
            }

            return [
                'replyText' => 'Seleccione un cliente (1-'.count($options).').',
                'actions' => [
                    [
                        'action' => 'needsChoice',
                        'payload' => [
                            'kind' => 'cliente',
                            'options' => $options,
                        ],
                        'resultado' => 'needsChoice',
                    ],
                ],
                'pendingChoice' => [
                    'kind' => 'cliente',
                    'options' => $options,
                    'forceChange' => $forceChange,
                ],
                'configurationRequired' => false,
            ];
        }

        $cliente = $matches[0];

        return $this->resolveSingleCliente($user, $draftContext, $cliente['codCliente'], $forceChange);
    }

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
     * @param  array<string, mixed>|null  $pendingChoice
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function chooseOption(User $user, array $draftContext, ?array $pendingChoice, int $option): array
    {
        $options = is_array($pendingChoice['options'] ?? null) ? $pendingChoice['options'] : [];
        $selected = null;

        foreach ($options as $item) {
            if ((int) ($item['n'] ?? 0) === $option) {
                $selected = $item;
                break;
            }
        }

        if ($selected === null) {
            return $this->refineResult('cliente');
        }

        $forceChange = (bool) ($pendingChoice['forceChange'] ?? false);

        return $this->resolveSingleCliente(
            $user,
            $draftContext,
            (string) ($selected['code'] ?? ''),
            $forceChange || ($draftContext['codCliente'] !== null && $draftContext['codCliente'] !== ''),
        );
    }

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
     * @param  array<string, mixed>|null  $pendingChoice
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function confirmChangeCliente(User $user, array $draftContext, ?array $pendingChoice): array
    {
        $candidate = is_array($pendingChoice['candidate'] ?? null) ? $pendingChoice['candidate'] : null;
        $codCliente = is_array($candidate) ? (string) ($candidate['codCliente'] ?? '') : '';

        if ($codCliente === '') {
            return $this->refineResult('cliente');
        }

        $cabeceraInicial = $this->cabeceraInicialService->buildForCliente($codCliente, $user);

        return [
            'replyText' => 'Cliente actualizado.',
            'actions' => [
                [
                    'action' => 'clearDraftForClienteChange',
                    'payload' => [],
                    'resultado' => 'ok',
                ],
                [
                    'action' => 'selectCliente',
                    'payload' => [
                        'codCliente' => $codCliente,
                        'cabeceraInicial' => $cabeceraInicial,
                    ],
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
    public function rejectChangeCliente(): array
    {
        return [
            'replyText' => 'Cambio de cliente cancelado.',
            'actions' => [
                [
                    'action' => 'rejectChangeCliente',
                    'payload' => [],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

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
    private function resolveSingleCliente(User $user, array $draftContext, string $codCliente, bool $forceChange): array
    {
        $current = $draftContext['codCliente'];
        $hasCliente = $current !== null && $current !== '';

        if ($hasCliente && ($forceChange || $current !== $codCliente)) {
            $cabeceraInicial = $this->cabeceraInicialService->buildForCliente($codCliente, $user);
            $candidate = [
                'codCliente' => $codCliente,
                'cabeceraInicial' => $cabeceraInicial,
            ];

            return [
                'replyText' => '¿Confirma cambiar el cliente? Responda sí o no.',
                'actions' => [
                    [
                        'action' => 'needsConfirm',
                        'payload' => [
                            'kind' => 'changeCliente',
                            'candidate' => $candidate,
                        ],
                        'resultado' => 'needsConfirm',
                    ],
                ],
                'pendingChoice' => [
                    'kind' => 'changeClienteConfirm',
                    'candidate' => $candidate,
                ],
                'configurationRequired' => false,
            ];
        }

        $cabeceraInicial = $this->cabeceraInicialService->buildForCliente($codCliente, $user);

        return [
            'replyText' => 'Cliente seleccionado.',
            'actions' => [
                [
                    'action' => 'selectCliente',
                    'payload' => [
                        'codCliente' => $codCliente,
                        'cabeceraInicial' => $cabeceraInicial,
                    ],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @return list<array{codCliente: string, razonSocial: string, nombre: string}>
     */
    private function searchClientes(User $user, string $q, int $limit): array
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return [];
        }

        $query = $this->visibleClientsResolver->visibleClientsForUser($user);
        $like = '%'.$q.'%';

        $query->where(function ($builder) use ($like, $q): void {
            $builder->where('cod_client', 'like', $like)
                ->orWhere('nombre', 'like', $like);

            if (Schema::hasColumn('pq_pedidosweb_clientes', 'razon_soci')) {
                $builder->orWhere('razon_soci', 'like', $like);
            }

            $builder->orWhere('cod_client', $q);
        });

        return $query
            ->orderBy('cod_client')
            ->limit($limit)
            ->get()
            ->map(static function ($cliente): array {
                $razon = (string) ($cliente->razon_soci ?? $cliente->nombre ?? '');

                return [
                    'codCliente' => (string) $cliente->cod_client,
                    'razonSocial' => $razon !== '' ? $razon : (string) ($cliente->nombre ?? ''),
                    'nombre' => (string) ($cliente->nombre ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function refineResult(string $kind): array
    {
        return [
            'replyText' => 'pedidos.carga.asistente.needsRefine',
            'actions' => [
                [
                    'action' => 'needsRefine',
                    'payload' => [
                        'kind' => $kind,
                        'hint' => 'pedidos.carga.asistente.needsRefine',
                    ],
                    'resultado' => 'needsRefine',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
