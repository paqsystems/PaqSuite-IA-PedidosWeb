<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Services\PedidosWeb\ArticuloCargaLookupService;
use App\Services\PedidosWeb\PedidosWebParameterService;

final class CargaAsistenteArticuloTool
{
    public function __construct(
        private readonly ArticuloCargaLookupService $articuloCargaLookupService,
        private readonly PedidosWebParameterService $parameterService,
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
    public function addRenglon(
        array $draftContext,
        string $q,
        float $cantidad,
        ?float $precioOverride = null,
        ?float $porcBonifOverride = null,
    ): array {
        $q = trim($q);
        $cantidad = $cantidad > 0 ? $cantidad : 1.0;

        if ($q === '') {
            return $this->notFoundResult();
        }

        $codLista = max(0, (int) $draftContext['codLista']);
        $matches = $this->buscarArticulosFiltrados($q, $codLista);
        $count = count($matches);

        if ($count === 0) {
            return $this->notFoundResult();
        }

        if ($count > 10) {
            return $this->tooManyResult();
        }

        if ($count > 1) {
            $options = [];
            foreach ($matches as $index => $articulo) {
                $options[] = [
                    'n' => $index + 1,
                    'label' => trim($articulo['codArticulo'].' — '.$articulo['descripcion']),
                    'code' => $articulo['codArticulo'],
                    'precio' => $articulo['precio'] ?? null,
                    'descripcion' => $articulo['descripcion'] ?? '',
                ];
            }

            return [
                'replyText' => 'pedidos.carga.asistente.elegirArticulo',
                'actions' => [
                    [
                        'action' => 'needsChoice',
                        'payload' => [
                            'kind' => 'articulo',
                            'options' => $options,
                        ],
                        'resultado' => 'needsChoice',
                    ],
                ],
                'pendingChoice' => [
                    'kind' => 'articulo',
                    'options' => $options,
                    'cantidad' => $cantidad,
                    'precio' => $precioOverride,
                    'porcBonif' => $porcBonifOverride,
                ],
                'configurationRequired' => false,
            ];
        }

        return $this->buildAddRenglon($matches[0], $cantidad, $precioOverride, $porcBonifOverride);
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
    public function chooseOption(array $draftContext, ?array $pendingChoice, int $option): array
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
            return $this->notFoundResult();
        }

        $cantidad = (float) ($pendingChoice['cantidad'] ?? 1);
        if ($cantidad <= 0) {
            $cantidad = 1.0;
        }

        $precioOverride = isset($pendingChoice['precio']) && $pendingChoice['precio'] !== null
            ? (float) $pendingChoice['precio']
            : null;

        $porcBonifOverride = isset($pendingChoice['porcBonif']) && $pendingChoice['porcBonif'] !== null
            ? (float) $pendingChoice['porcBonif']
            : null;

        return $this->buildAddRenglon([
            'codArticulo' => (string) ($selected['code'] ?? ''),
            'descripcion' => (string) ($selected['descripcion'] ?? $selected['label'] ?? ''),
            'precio' => $selected['precio'] ?? null,
            'bonificacion' => $selected['bonificacion'] ?? null,
        ], $cantidad, $precioOverride, $porcBonifOverride);
    }

    /**
     * Eliminar o actualizar un renglón ya cargado en el borrador.
     *
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function mutateExistingRenglon(
        array $draftContext,
        string $operation,
        string $q,
        bool $ultimo = false,
        ?float $cantidad = null,
        ?float $precio = null,
        ?float $porcBonif = null,
    ): array {
        $operation = $operation === 'remove' ? 'remove' : 'update';

        if ($operation === 'update') {
            $denied = $this->assertUpdatePermisos($draftContext, $precio, $porcBonif);
            if ($denied !== null) {
                return $denied;
            }

            if ($cantidad === null && $precio === null && $porcBonif === null) {
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
        }

        $matches = $this->findDraftRenglones($draftContext, $q, $ultimo);
        $count = count($matches);

        if ($count === 0) {
            return $this->renglonNoEncontradoResult(trim($q));
        }

        if ($count > 10) {
            return $this->tooManyResult();
        }

        if ($count > 1) {
            $options = [];
            foreach ($matches as $index => $row) {
                $options[] = [
                    'n' => $index + 1,
                    'label' => $this->formatRenglonChoiceLabel($row),
                    'code' => (string) ($row['codArticulo'] ?? ''),
                    'renglon' => (int) ($row['renglon'] ?? 0),
                ];
            }

            return [
                'replyText' => 'pedidos.carga.asistente.elegirRenglon',
                'actions' => [
                    [
                        'action' => 'needsChoice',
                        'payload' => [
                            'kind' => 'renglonExistente',
                            'options' => $options,
                        ],
                        'resultado' => 'needsChoice',
                    ],
                ],
                'pendingChoice' => [
                    'kind' => 'renglonExistente',
                    'operation' => $operation,
                    'options' => $options,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'porcBonif' => $porcBonif,
                ],
                'configurationRequired' => false,
            ];
        }

        return $this->buildMutateAction($operation, $matches[0], $cantidad, $precio, $porcBonif);
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @param  array<string, mixed>|null  $pendingChoice
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function chooseExistingRenglonOption(
        array $draftContext,
        ?array $pendingChoice,
        int $option,
    ): array {
        unset($draftContext);
        $options = is_array($pendingChoice['options'] ?? null) ? $pendingChoice['options'] : [];
        $selected = null;

        foreach ($options as $item) {
            if ((int) ($item['n'] ?? 0) === $option) {
                $selected = $item;
                break;
            }
        }

        if ($selected === null) {
            return $this->renglonNoEncontradoResult(null);
        }

        $operation = ((string) ($pendingChoice['operation'] ?? 'update')) === 'remove'
            ? 'remove'
            : 'update';

        $cantidad = isset($pendingChoice['cantidad']) && $pendingChoice['cantidad'] !== null
            ? (float) $pendingChoice['cantidad']
            : null;
        $precio = isset($pendingChoice['precio']) && $pendingChoice['precio'] !== null
            ? (float) $pendingChoice['precio']
            : null;
        $porcBonif = isset($pendingChoice['porcBonif']) && $pendingChoice['porcBonif'] !== null
            ? (float) $pendingChoice['porcBonif']
            : null;

        return $this->buildMutateAction(
            $operation,
            [
                'renglon' => (int) ($selected['renglon'] ?? 0),
                'codArticulo' => (string) ($selected['code'] ?? ''),
            ],
            $cantidad,
            $precio,
            $porcBonif,
        );
    }

    /**
     * @param  array<string, mixed>  $articulo
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function buildAddRenglon(
        array $articulo,
        float $cantidad,
        ?float $precioOverride = null,
        ?float $porcBonifOverride = null,
    ): array {
        $payload = [
            'codArticulo' => (string) ($articulo['codArticulo'] ?? ''),
            'cantidad' => $cantidad,
            'descripcion' => (string) ($articulo['descripcion'] ?? ''),
        ];

        if ($precioOverride !== null) {
            $payload['precio'] = $precioOverride;
        } elseif (isset($articulo['precio'])) {
            $payload['precio'] = (float) $articulo['precio'];
        }

        if ($porcBonifOverride !== null) {
            $payload['porcBonif'] = $porcBonifOverride;
        } elseif (isset($articulo['bonificacion'])) {
            $payload['porcBonif'] = (float) $articulo['bonificacion'];
        } elseif (isset($articulo['porcBonif'])) {
            $payload['porcBonif'] = (float) $articulo['porcBonif'];
        }

        return [
            'replyText' => 'pedidos.carga.asistente.articuloAgregado',
            'actions' => [
                [
                    'action' => 'addRenglon',
                    'payload' => $payload,
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function buildMutateAction(
        string $operation,
        array $row,
        ?float $cantidad,
        ?float $precio,
        ?float $porcBonif,
    ): array {
        $renglon = (int) ($row['renglon'] ?? 0);
        if ($renglon <= 0) {
            return $this->renglonNoEncontradoResult(null);
        }

        if ($operation === 'remove') {
            return [
                'replyText' => 'pedidos.carga.asistente.renglonEliminado',
                'actions' => [
                    [
                        'action' => 'removeRenglon',
                        'payload' => ['renglon' => $renglon],
                        'resultado' => 'ok',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        $payload = ['renglon' => $renglon];
        if ($cantidad !== null && $cantidad > 0) {
            $payload['cantidad'] = $cantidad;
        }
        if ($precio !== null && $precio >= 0) {
            $payload['precio'] = $precio;
        }
        if ($porcBonif !== null) {
            $payload['porcBonif'] = $porcBonif;
        }

        return [
            'replyText' => 'pedidos.carga.asistente.renglonActualizado',
            'actions' => [
                [
                    'action' => 'updateRenglon',
                    'payload' => $payload,
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return list<array<string, mixed>>
     */
    private function findDraftRenglones(array $draftContext, string $q, bool $ultimo): array
    {
        $rows = [];
        foreach (($draftContext['renglones'] ?? []) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $cod = trim((string) ($row['codArticulo'] ?? ''));
            if ($cod === '') {
                continue;
            }
            $rows[] = [
                'renglon' => (int) ($row['renglon'] ?? ($index + 1)),
                'codArticulo' => $cod,
                'descripcion' => trim((string) ($row['descripcion'] ?? '')),
                'cantidad' => (float) ($row['cantidad'] ?? 0),
                'precio' => isset($row['precio']) ? (float) $row['precio'] : null,
                'porcBonif' => isset($row['porcBonif']) ? (float) $row['porcBonif'] : null,
            ];
        }

        if ($rows === []) {
            return [];
        }

        if ($ultimo) {
            return [end($rows)];
        }

        $q = mb_strtolower(trim($q));
        $q = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $q);
        if ($q === '') {
            return $rows;
        }

        $tokens = preg_split('/\s+/u', $q) ?: [];
        $tokens = array_values(array_filter(
            $tokens,
            static fn (string $token): bool => mb_strlen($token) >= 2
                && ! in_array($token, [
                    'del', 'de', 'la', 'el', 'los', 'las',
                    'articulo', 'artículo', 'producto', 'renglon', 'renglón',
                ], true),
        ));

        return array_values(array_filter(
            $rows,
            static function (array $row) use ($q, $tokens): bool {
                $cod = mb_strtolower((string) $row['codArticulo']);
                $desc = mb_strtolower(str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'ü'],
                    ['a', 'e', 'i', 'o', 'u', 'u'],
                    (string) $row['descripcion'],
                ));

                if ($cod === $q || str_contains($cod, $q) || str_contains($desc, $q)) {
                    return true;
                }

                if ($tokens === []) {
                    return false;
                }

                foreach ($tokens as $token) {
                    if (! str_contains($cod, $token) && ! str_contains($desc, $token)) {
                        return false;
                    }
                }

                return true;
            },
        ));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function formatRenglonChoiceLabel(array $row): string
    {
        $cod = (string) ($row['codArticulo'] ?? '');
        $desc = trim((string) ($row['descripcion'] ?? ''));
        $cant = $row['cantidad'] ?? 0;
        $precio = $row['precio'];
        $bonif = $row['porcBonif'];

        $parts = [
            trim($cod.($desc !== '' ? ' — '.$desc : '')),
            'cant '.$this->formatNumberLabel((float) $cant),
        ];

        if ($precio !== null) {
            $parts[] = 'precio '.$this->formatNumberLabel((float) $precio);
        }

        if ($bonif !== null) {
            $parts[] = 'bonif '.$this->formatNumberLabel((float) $bonif).'%';
        }

        return implode(' · ', $parts);
    }

    private function formatNumberLabel(float $value): string
    {
        if (abs($value - round($value)) < 0.00001) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }|null
     */
    private function assertUpdatePermisos(
        array $draftContext,
        ?float $precio,
        ?float $porcBonif,
    ): ?array {
        if ($precio === null && $porcBonif === null) {
            return null;
        }

        $perfil = strtoupper((string) ($draftContext['perfilUsuario'] ?? 'V'));
        $functionalProfile = match ($perfil) {
            'C' => 'cliente',
            'S' => 'supervisor',
            default => 'vendedor',
        };
        $flags = $this->parameterService->resolveModificaFlags($functionalProfile);

        if ($precio !== null && ! ($flags['modificaPrecio'] ?? false)) {
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

        if ($porcBonif !== null && ! ($flags['modificaBonArt'] ?? false)) {
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

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buscarArticulosFiltrados(string $q, int $codLista): array
    {
        $q = $this->normalizeArticuloQuery($q);
        if ($q === '') {
            return [];
        }

        $tokens = $this->significantTokens($q);
        $seed = $this->resolveSearchSeed($q, $tokens);
        $raw = $this->articuloCargaLookupService->buscar($seed, 100, $codLista);
        $raw = $this->excludeUsaEscBase($raw);

        if ($raw === []) {
            // Fallback: frase completa por si el seed fue demasiado estrecho.
            if ($seed !== $q) {
                $raw = $this->excludeUsaEscBase(
                    $this->articuloCargaLookupService->buscar($q, 100, $codLista),
                );
            }

            return $raw;
        }

        if ($tokens === []) {
            return array_slice($raw, 0, 11);
        }

        $filtered = array_values(array_filter(
            $raw,
            function (array $row) use ($tokens): bool {
                $haystack = mb_strtolower(trim(
                    (string) ($row['codArticulo'] ?? '').' '.(string) ($row['descripcion'] ?? ''),
                ));

                foreach ($tokens as $token) {
                    if (! str_contains($haystack, $token)) {
                        return false;
                    }
                }

                return true;
            },
        ));

        if ($filtered !== []) {
            // Preferir coincidencia exacta de descripción.
            $exact = array_values(array_filter(
                $filtered,
                static function (array $row) use ($q): bool {
                    return mb_strtolower(trim((string) ($row['descripcion'] ?? ''))) === mb_strtolower($q);
                },
            ));

            if (count($exact) === 1) {
                return $exact;
            }

            return array_slice($filtered, 0, 11);
        }

        // La semilla trajo filas, pero ninguna contiene todos los tokens.
        return [];
    }

    private function normalizeArticuloQuery(string $q): string
    {
        $q = trim($q);
        $q = trim($q, " \t\"'`");
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;

        return trim($q);
    }

    /**
     * @return list<string>
     */
    private function significantTokens(string $q): array
    {
        $parts = preg_split('/[\s,;]+/u', mb_strtolower($q)) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            $part = trim($part, " \t\"'`");
            if ($part === '' || mb_strlen($part) < 2) {
                continue;
            }
            if (in_array($part, ['de', 'la', 'el', 'los', 'las', 'un', 'una'], true)) {
                continue;
            }
            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    /**
     * @param  list<string>  $tokens
     */
    private function resolveSearchSeed(string $q, array $tokens): string
    {
        foreach ($tokens as $token) {
            if (preg_match('/^\d+\/\d+/u', $token) === 1) {
                return $token;
            }
        }

        // Semilla = token más largo (suele ser la marca/descripción distintiva).
        $longest = $q;
        $maxLen = 0;
        foreach ($tokens as $token) {
            $len = mb_strlen($token);
            if ($len > $maxLen) {
                $maxLen = $len;
                $longest = $token;
            }
        }

        return $longest;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function excludeUsaEscBase(array $rows): array
    {
        return array_values(array_filter(
            $rows,
            static function (array $row): bool {
                $usaEsc = mb_strtoupper(trim((string) ($row['usa_esc'] ?? $row['usaEsc'] ?? '')));

                return $usaEsc !== 'B';
            },
        ));
    }

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function renglonNoEncontradoResult(?string $q): array
    {
        $q = trim((string) $q);
        $hasQ = $q !== '';
        $replyKey = $hasQ
            ? 'pedidos.carga.asistente.renglonNoEncontradoConQ'
            : 'pedidos.carga.asistente.renglonNoEncontrado';

        $payload = [
            'kind' => 'renglonExistente',
            'hint' => $replyKey,
        ];
        if ($hasQ) {
            $payload['q'] = $q;
        }

        return [
            'replyText' => $replyKey,
            'actions' => [
                [
                    'action' => 'needsRefine',
                    'payload' => $payload,
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
    private function notFoundResult(): array
    {
        return [
            'replyText' => 'pedidos.carga.asistente.articuloNoEncontrado',
            'actions' => [
                [
                    'action' => 'needsRefine',
                    'payload' => [
                        'kind' => 'articulo',
                        'hint' => 'pedidos.carga.asistente.articuloNoEncontrado',
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
    private function tooManyResult(): array
    {
        return [
            'replyText' => 'pedidos.carga.asistente.needsRefine',
            'actions' => [
                [
                    'action' => 'needsRefine',
                    'payload' => [
                        'kind' => 'articulo',
                        'hint' => 'pedidos.carga.asistente.needsRefine',
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
    private function refineResult(): array
    {
        return $this->tooManyResult();
    }
}
