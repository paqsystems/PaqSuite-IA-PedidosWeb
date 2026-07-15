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
     * Lookup compartido (carga por texto / extracto imagen).
     *
     * @return list<array{
     *     codArticulo: string,
     *     descripcion: string,
     *     porcIva: float,
     *     bonificacion: float,
     *     precio: float,
     *     disponibleNeto: float,
     *     disponibleNetoBase: float|null
     * }>
     */
    public function buscarCandidatos(string $q, int $codLista): array
    {
        return $this->buscarArticulosFiltrados($q, $codLista);
    }

    /**
     * @return list<array{
     *     codArticulo: string,
     *     descripcion: string,
     *     porcIva: float,
     *     bonificacion: float,
     *     precio: float,
     *     disponibleNeto: float,
     *     disponibleNetoBase: float|null
     * }>
     */
    private function buscarArticulosFiltrados(string $q, int $codLista): array
    {
        $q = $this->normalizeArticuloQuery($q);
        if ($q === '') {
            return [];
        }

        $variants = $this->buildArticuloSearchVariants($q);
        $best = [];

        foreach ($variants as $variant) {
            $tokens = $this->significantTokens($variant);
            $seed = $this->resolveSearchSeed($variant, $tokens);
            $raw = $this->articuloCargaLookupService->buscar($seed, 100, $codLista);
            $raw = $this->excludeUsaEscBase($raw);

            if ($raw === []) {
                if ($seed !== $variant) {
                    $raw = $this->excludeUsaEscBase(
                        $this->articuloCargaLookupService->buscar($variant, 100, $codLista),
                    );
                }
            }

            if ($raw === []) {
                continue;
            }

            if ($tokens === []) {
                $candidate = array_slice($raw, 0, 11);
            } else {
                $filtered = array_values(array_filter(
                    $raw,
                    function (array $row) use ($tokens): bool {
                        $codigo = (string) ($row['codArticulo'] ?? '');
                        $descripcion = (string) ($row['descripcion'] ?? '');
                        $haystackText = $this->normalizeArticuloToken($descripcion);
                        $haystackAll = $this->normalizeArticuloHaystack($codigo, $descripcion);

                        foreach ($tokens as $token) {
                            $normalizedToken = $this->normalizeArticuloToken($token);
                            if ($normalizedToken === '') {
                                continue;
                            }

                            if (preg_match('/^\d+$/u', $normalizedToken) === 1) {
                                // Evitar falsos positivos por dígitos del código (ej. AF01 vs pack "1").
                                if (! $this->descriptionContainsPackNumber($descripcion, $normalizedToken)) {
                                    return false;
                                }
                                continue;
                            }

                            if (! str_contains($haystackAll, $normalizedToken)
                                && ! str_contains($haystackText, $normalizedToken)
                                && ! $this->fuzzyTokenMatchesDescription($descripcion, $normalizedToken)
                            ) {
                                return false;
                            }
                        }

                        return true;
                    },
                ));

                if ($filtered === []) {
                    continue;
                }

                $exact = array_values(array_filter(
                    $filtered,
                    function (array $row) use ($variant): bool {
                        return $this->normalizeArticuloToken((string) ($row['descripcion'] ?? ''))
                            === $this->normalizeArticuloToken($variant);
                    },
                ));

                $candidate = count($exact) === 1 ? $exact : array_slice($filtered, 0, 11);
            }

            $candidate = $this->preferCloserArticuloCandidates($variant, $candidate);

            if (count($candidate) === 1) {
                return $candidate;
            }

            if ($best === [] || count($candidate) < count($best)) {
                $best = $candidate;
            }
        }

        return $best;
    }

    /**
     * @param  list<array<string, mixed>>  $candidates
     * @return list<array<string, mixed>>
     */
    private function preferCloserArticuloCandidates(string $q, array $candidates): array
    {
        if (count($candidates) <= 1) {
            return $candidates;
        }

        $queryTokens = $this->significantTokens($q);
        $scored = [];

        foreach ($candidates as $row) {
            $scored[] = [
                'row' => $row,
                'penalty' => $this->scoreArticuloExtraWords(
                    (string) ($row['descripcion'] ?? ''),
                    $queryTokens,
                ),
            ];
        }

        $minPenalty = min(array_column($scored, 'penalty'));
        $preferred = array_values(array_map(
            static fn (array $item): array => $item['row'],
            array_filter(
                $scored,
                static fn (array $item): bool => $item['penalty'] === $minPenalty,
            ),
        ));

        return $preferred !== [] ? $preferred : $candidates;
    }

    /**
     * @param  list<string>  $queryTokens
     */
    private function scoreArticuloExtraWords(string $descripcion, array $queryTokens): int
    {
        $descTokens = $this->significantTokens($descripcion);
        $penalty = 0;

        foreach ($descTokens as $descToken) {
            if (preg_match('/^\d+$/u', $descToken) === 1) {
                continue;
            }
            if (in_array($descToken, ['kg', 'g', 'gr', 'lt', 'l', 'ml', 'un', 'u', 'ud', 'uds'], true)) {
                continue;
            }

            $covered = false;
            foreach ($queryTokens as $queryToken) {
                $qt = $this->normalizeArticuloToken($queryToken);
                $dt = $this->normalizeArticuloToken($descToken);
                if ($qt === '' || $dt === '') {
                    continue;
                }
                if ($qt === $dt || (mb_strlen($qt) >= 5 && levenshtein($qt, $dt) <= 1)) {
                    $covered = true;
                    break;
                }
            }

            if (! $covered) {
                $penalty++;
            }
        }

        return $penalty;
    }

    /**
     * @return list<string>
     */
    private function buildArticuloSearchVariants(string $q): array
    {
        $variants = [$q];

        // Catálogo Ankas: "AJO EN POLVO25 KG" (pack pegado al texto).
        $glued = preg_replace(
            '/(\S+)\s+(\d+)\s*(kg|g|gr|lt|l|ml|un|u|uds?)\b/iu',
            '$1$2 $3',
            $q,
        );
        if (is_string($glued) && $glued !== $q) {
            $variants[] = $glued;
        }

        $compact = preg_replace('/\s+/u', '', $q);
        if (is_string($compact) && $compact !== '' && $compact !== $q) {
            $variants[] = $compact;
        }

        return array_values(array_unique($variants));
    }

    private function normalizeArticuloHaystack(string $codigo, string $descripcion): string
    {
        return $this->normalizeArticuloToken($codigo.' '.$descripcion);
    }

    private function normalizeArticuloToken(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', '', $value) ?? $value;

        return $value;
    }

    private function descriptionContainsPackNumber(string $descripcion, string $number): bool
    {
        $descripcion = mb_strtolower($descripcion);

        return preg_match('/(?<!\d)'.preg_quote($number, '/').'(?!\d)/u', $descripcion) === 1;
    }

    /**
     * Tolerancia OCR: "ramilada" ≈ "ramillada" (1 edición).
     */
    private function fuzzyTokenMatchesDescription(string $descripcion, string $token): bool
    {
        if (mb_strlen($token) < 5) {
            return false;
        }

        $words = preg_split('/[\s,;]+/u', mb_strtolower($descripcion)) ?: [];

        foreach ($words as $word) {
            $word = preg_replace('/\d+(?:kg|g|gr|lt|l|ml|un|u|uds?)?$/iu', '', $word) ?? $word;
            $word = $this->normalizeArticuloToken((string) $word);
            if ($word === '' || mb_strlen($word) < 5) {
                continue;
            }

            if (levenshtein($token, $word) <= 1) {
                return true;
            }
        }

        return false;
    }

    private function normalizeArticuloQuery(string $q): string
    {
        $q = trim($q);
        $q = trim($q, " \t\"'`");
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;
        // "POLVO 25kg" / "POLVO 25 kg" → "POLVO25 kg" (alineado a maestro).
        $q = preg_replace(
            '/(\S+)\s+(\d+)\s*(kg|g|gr|lt|l|ml|un|u|uds?)\b/iu',
            '$1$2 $3',
            $q,
        ) ?? $q;

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
            if ($part === '') {
                continue;
            }

            if (preg_match('/^\d+$/u', $part) === 1) {
                $tokens[] = $part;
                continue;
            }

            if (mb_strlen($part) < 2) {
                continue;
            }
            if (in_array($part, ['de', 'la', 'el', 'los', 'las', 'un', 'una', 'en'], true)) {
                continue;
            }

            if (preg_match('/^(\d+)(kg|g|gr|lt|l|ml|un|u|uds?)$/iu', $part, $matches) === 1) {
                $tokens[] = $matches[1];
                $tokens[] = mb_strtolower($matches[2]);
                continue;
            }

            // "polvo25" → polvo + 25
            if (preg_match('/^(.*?)(\d+)$/u', $part, $matches) === 1 && $matches[1] !== '') {
                $tokens[] = $matches[1];
                $tokens[] = $matches[2];
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

        // Semilla = token alfabético más largo (evitar semilla solo numérica/unidad).
        $longest = $q;
        $maxLen = 0;
        foreach ($tokens as $token) {
            if (preg_match('/^\d+$/u', $token) === 1) {
                continue;
            }
            if (in_array($token, ['kg', 'g', 'gr', 'lt', 'l', 'ml', 'un', 'u', 'ud', 'uds'], true)) {
                continue;
            }
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
