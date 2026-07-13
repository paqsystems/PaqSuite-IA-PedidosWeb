<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\PqPedidoswebClienteDireccionEntrega;
use App\Models\PqPedidoswebCondicionVenta;
use App\Models\PqPedidoswebListaPrecios;
use App\Models\PqPedidoswebPerfil;
use App\Models\PqPedidoswebTransporte;
use App\Services\PedidosWeb\PedidosWebParameterService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class CargaAsistenteCabeceraTool
{
    /** @var list<string> */
    private const CABECERA_FIELDS = [
        'listaPrecios',
        'listaPreciosDescripcion',
        'moneda',
        'incluyeIva',
        'fechaEntrega',
        'bonif1',
        'bonif2',
        'bonif3',
        'expreso',
        'expresoDire',
        'codTranspor',
        'codCondvta',
        'codPerfil',
        'idDe',
        'direccionEntrega',
    ];

    private const BONIF3_MIN = -99.99;

    private const BONIF3_MAX = 99.99;

    public function __construct(
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setCampoLibre(string $field, mixed $value, array $draftContext = []): array
    {
        $field = trim($field);

        if ($field === '') {
            return $this->validationHelp();
        }

        $flags = $this->resolveFlags($draftContext);

        if (in_array($field, ['bonif1', 'bonif2', 'bonif3'], true)) {
            if (! ($flags['modificaBonCli'] ?? false)) {
                return $this->denied();
            }

            $parsed = $this->parseBonificacionValue($value, $field);
            if ($parsed === null) {
                return $this->validationHelp();
            }
            $value = $parsed;
        }

        if (in_array($field, ['expreso', 'expresoDire'], true)) {
            if (! ($flags['modificaExpreso'] ?? false)) {
                return $this->denied();
            }
            $value = trim((string) $value);
        }

        $isCabeceraField = in_array($field, self::CABECERA_FIELDS, true);

        return [
            'replyText' => 'Campo actualizado.',
            'actions' => [
                [
                    'action' => $isCabeceraField ? 'setCabeceraField' : 'setCampoLibre',
                    'payload' => [
                        'field' => $field,
                        'value' => $value,
                    ],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setTransporte(string $q, array $draftContext = []): array
    {
        // Transporte en UI no usa flag aparte; solo lectura la bloquea el turn.
        unset($draftContext);

        return $this->resolveCatalogSelection(
            kind: 'transporte',
            q: $q,
            table: 'pq_pedidosweb_transportes',
            notFoundKey: 'pedidos.carga.asistente.transporteNoEncontrado',
            chooseKey: 'pedidos.carga.asistente.elegirTransporte',
            fetch: function (string $like, string $exact): Collection {
                return PqPedidoswebTransporte::query()
                    ->where(function ($query) use ($like, $exact): void {
                        $query->where('codigo', $exact)
                            ->orWhere('codigo', 'like', $like)
                            ->orWhere('descripcion', 'like', $like);
                    })
                    ->orderBy('descripcion')
                    ->limit(11)
                    ->get(['codigo', 'descripcion']);
            },
            mapOption: static fn ($row): array => [
                'code' => (string) $row->codigo,
                'label' => trim((string) $row->codigo.' — '.(string) $row->descripcion),
                'fields' => ['codTranspor' => (string) $row->codigo],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setCondicionVenta(string $q, array $draftContext): array
    {
        $flags = $this->resolveFlags($draftContext);
        if (! ($flags['modificaCondVta'] ?? false)) {
            return $this->denied();
        }

        return $this->resolveCatalogSelection(
            kind: 'condicionVenta',
            q: $q,
            table: 'pq_pedidosweb_condventa',
            notFoundKey: 'pedidos.carga.asistente.condicionNoEncontrada',
            chooseKey: 'pedidos.carga.asistente.elegirCondicion',
            fetch: function (string $like, string $exact): Collection {
                $query = PqPedidoswebCondicionVenta::query()
                    ->where(function ($builder) use ($like, $exact): void {
                        $builder->where('descripcion', 'like', $like);
                        if (is_numeric($exact)) {
                            $builder->orWhere('codigo', (int) $exact);
                        }
                    })
                    ->orderBy('descripcion')
                    ->limit(11);

                return $query->get(['codigo', 'descripcion']);
            },
            mapOption: static fn ($row): array => [
                'code' => (string) $row->codigo,
                'label' => trim((string) $row->codigo.' — '.(string) $row->descripcion),
                'fields' => ['codCondvta' => (int) $row->codigo],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setPerfil(string $q, array $draftContext): array
    {
        // Sin parámetro ModificaPerfil: admisible si no es solo lectura (turn).
        unset($draftContext);

        return $this->resolveCatalogSelection(
            kind: 'perfil',
            q: $q,
            table: 'pq_pedidosweb_perfil',
            notFoundKey: 'pedidos.carga.asistente.perfilNoEncontrado',
            chooseKey: 'pedidos.carga.asistente.elegirPerfil',
            fetch: function (string $like, string $exact): Collection {
                return PqPedidoswebPerfil::query()
                    ->where(function ($query) use ($like, $exact): void {
                        $query->where('cod_perfil', $exact)
                            ->orWhere('cod_perfil', 'like', $like)
                            ->orWhere('descripcion', 'like', $like);
                    })
                    ->orderBy('descripcion')
                    ->limit(11)
                    ->get(['cod_perfil', 'descripcion']);
            },
            mapOption: static fn ($row): array => [
                'code' => (string) $row->cod_perfil,
                'label' => trim((string) $row->cod_perfil.' — '.(string) $row->descripcion),
                'fields' => ['codPerfil' => (string) $row->cod_perfil],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setListaPrecios(string $q, array $draftContext): array
    {
        $flags = $this->resolveFlags($draftContext);
        if (! ($flags['modificaListaPrec'] ?? false)) {
            return $this->denied();
        }

        return $this->resolveCatalogSelection(
            kind: 'listaPrecios',
            q: $q,
            table: 'pq_pedidosweb_listaprecios',
            notFoundKey: 'pedidos.carga.asistente.listaNoEncontrada',
            chooseKey: 'pedidos.carga.asistente.elegirLista',
            fetch: function (string $like, string $exact): Collection {
                $query = PqPedidoswebListaPrecios::query()
                    ->where(function ($builder) use ($like, $exact): void {
                        $builder->where('descripcion', 'like', $like);
                        if (is_numeric($exact)) {
                            $builder->orWhere('cod_lista', (int) $exact);
                        }
                    })
                    ->orderBy('descripcion')
                    ->limit(11);

                return $query->get(['cod_lista', 'descripcion', 'moneda', 'incluye_iva']);
            },
            mapOption: static fn ($row): array => [
                'code' => (string) $row->cod_lista,
                'label' => trim((string) $row->cod_lista.' — '.(string) $row->descripcion),
                'fields' => [
                    'listaPrecios' => (int) $row->cod_lista,
                    'listaPreciosDescripcion' => (string) $row->descripcion,
                    'moneda' => (int) $row->moneda,
                    'incluyeIva' => (bool) $row->incluye_iva,
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setDireccionEntrega(string $q, array $draftContext): array
    {
        $flags = $this->resolveFlags($draftContext);
        if (! ($flags['modificaDirEntr'] ?? false)) {
            return $this->denied();
        }

        $codCliente = trim((string) ($draftContext['codCliente'] ?? ''));
        if ($codCliente === '') {
            return [
                'replyText' => 'pedidos.carga.asistente.needsCliente',
                'actions' => [
                    [
                        'action' => 'needsRefine',
                        'payload' => [
                            'kind' => 'direccionEntrega',
                            'hint' => 'pedidos.carga.asistente.needsCliente',
                        ],
                        'resultado' => 'needsRefine',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        return $this->resolveCatalogSelection(
            kind: 'direccionEntrega',
            q: $q,
            table: 'pq_pedidosweb_clientesde',
            notFoundKey: 'pedidos.carga.asistente.direccionNoEncontrada',
            chooseKey: 'pedidos.carga.asistente.elegirDireccion',
            fetch: function (string $like, string $exact) use ($codCliente): Collection {
                $query = PqPedidoswebClienteDireccionEntrega::query()
                    ->where('cod_client', $codCliente)
                    ->where(function ($builder) use ($like, $exact): void {
                        $builder->where('direccion', 'like', $like)
                            ->orWhere('localidad', 'like', $like);
                        if (is_numeric($exact)) {
                            $builder->orWhere('id_de', (int) $exact);
                        }
                    })
                    ->orderByDesc('habitual')
                    ->orderBy('direccion')
                    ->limit(11);

                return $query->get(['id_de', 'direccion', 'localidad']);
            },
            mapOption: static function ($row): array {
                $direccion = trim((string) $row->direccion);
                $localidad = trim((string) ($row->localidad ?? ''));
                $label = $localidad !== '' ? $direccion.' — '.$localidad : $direccion;

                return [
                    'code' => (string) $row->id_de,
                    'label' => $label,
                    'fields' => [
                        'idDe' => (int) $row->id_de,
                        'direccionEntrega' => $direccion,
                    ],
                ];
            },
        );
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function setFechaEntrega(string $raw, array $draftContext = []): array
    {
        unset($draftContext);
        $iso = $this->parseFechaEntrega($raw);
        if ($iso === null) {
            return $this->validationHelp();
        }

        return $this->applyFields(
            ['fechaEntrega' => $iso],
            'Campo actualizado: '.$iso,
        );
    }

    /**
     * @param  array<string, mixed>|null  $pendingChoice
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function chooseCatalogOption(?array $pendingChoice, int $option): array
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
            return $this->validationHelp();
        }

        $fields = is_array($selected['fields'] ?? null) ? $selected['fields'] : [];
        if ($fields === [] && isset($selected['code'])) {
            // Compat transporte antiguo.
            $fields = ['codTranspor' => (string) $selected['code']];
        }

        if ($fields === []) {
            return $this->validationHelp();
        }

        return $this->applyFields($fields, 'Campo actualizado: '.(string) ($selected['label'] ?? ''));
    }

    /** @deprecated usar chooseCatalogOption */
    public function chooseTransporteOption(?array $pendingChoice, int $option): array
    {
        return $this->chooseCatalogOption($pendingChoice, $option);
    }

    /**
     * @param  callable(string, string): Collection  $fetch
     * @param  callable(mixed): array{code: string, label: string, fields: array<string, mixed>}  $mapOption
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function resolveCatalogSelection(
        string $kind,
        string $q,
        string $table,
        string $notFoundKey,
        string $chooseKey,
        callable $fetch,
        callable $mapOption,
    ): array {
        $q = trim($q);
        if ($q === '') {
            return $this->validationHelp();
        }

        if (! Schema::hasTable($table)) {
            return $this->notFound($kind, $notFoundKey);
        }

        $like = '%'.$q.'%';
        /** @var Collection $rows */
        $rows = $fetch($like, $q);
        $count = $rows->count();

        if ($count === 0) {
            return $this->notFound($kind, $notFoundKey);
        }

        if ($count > 10) {
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

        $mapped = [];
        foreach ($rows->values() as $index => $row) {
            $option = $mapOption($row);
            $mapped[] = [
                'n' => $index + 1,
                'label' => (string) $option['label'],
                'code' => (string) $option['code'],
                'fields' => $option['fields'],
            ];
        }

        if ($count > 1) {
            return [
                'replyText' => $chooseKey,
                'actions' => [
                    [
                        'action' => 'needsChoice',
                        'payload' => [
                            'kind' => $kind,
                            'options' => $mapped,
                        ],
                        'resultado' => 'needsChoice',
                    ],
                ],
                'pendingChoice' => [
                    'kind' => $kind,
                    'options' => $mapped,
                ],
                'configurationRequired' => false,
            ];
        }

        return $this->applyFields(
            $mapped[0]['fields'],
            'Campo actualizado: '.$mapped[0]['label'],
        );
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function applyFields(array $fields, string $replyText): array
    {
        return [
            'replyText' => $replyText,
            'actions' => [
                [
                    'action' => 'setCabeceraFields',
                    'payload' => ['fields' => $fields],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     modificaBonCli: bool,
     *     modificaListaPrec: bool,
     *     modificaCondVta: bool,
     *     modificaDirEntr: bool,
     *     modificaExpreso: bool
     * }
     */
    private function resolveFlags(array $draftContext): array
    {
        $perfil = strtoupper((string) ($draftContext['perfilUsuario'] ?? 'V'));
        $functionalProfile = match ($perfil) {
            'C' => 'cliente',
            'S' => 'supervisor',
            default => 'vendedor',
        };

        return $this->parameterService->resolveModificaFlags($functionalProfile);
    }

    private function parseFechaEntrega(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('/^[:\s]+/u', '', $raw) ?? $raw;

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y', 'd-m-y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date !== false) {
                    return $date->startOfDay()->toDateString();
                }
            } catch (\Throwable) {
                // try next
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay()->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseBonificacionValue(mixed $value, string $field): ?float
    {
        if (is_int($value) || is_float($value)) {
            $number = (float) $value;
        } else {
            $raw = trim((string) $value);
            $raw = preg_replace('/\s*%\s*$/u', '', $raw) ?? $raw;
            $raw = str_replace(',', '.', $raw);
            if ($raw === '' || ! is_numeric($raw)) {
                return null;
            }
            $number = (float) $raw;
        }

        if ($field === 'bonif3') {
            if ($number < self::BONIF3_MIN || $number > self::BONIF3_MAX) {
                return null;
            }

            return round($number, 2);
        }

        if ($number < 0) {
            return null;
        }

        return round($number, 2);
    }

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function notFound(string $kind, string $messageKey): array
    {
        return [
            'replyText' => $messageKey,
            'actions' => [
                [
                    'action' => 'needsRefine',
                    'payload' => [
                        'kind' => $kind,
                        'hint' => $messageKey,
                    ],
                    'resultado' => 'needsRefine',
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
    private function validationHelp(): array
    {
        return [
            'replyText' => 'pedidos.carga.asistente.help',
            'actions' => [
                [
                    'action' => 'validationError',
                    'payload' => ['messageKey' => 'pedidos.carga.asistente.help'],
                    'resultado' => 'validationError',
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
    private function denied(): array
    {
        return [
            'replyText' => 'pedidos.carga.asistente.denied',
            'actions' => [
                [
                    'action' => 'denied',
                    'payload' => ['messageKey' => 'pedidos.carga.asistente.denied'],
                    'resultado' => 'denied',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
