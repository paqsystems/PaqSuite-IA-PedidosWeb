<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\User;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;
use App\Services\PedidosWeb\PedidosWebParameterService;

final class CargaAsistenteImageExtractTool
{
    private const EXTRACTION_SYSTEM_PROMPT = <<<'PROMPT'
Sos un extractor estructurado de pedidos/presupuestos para PedidosWeb.
Leé la(s) imagen(es) adjuntas y devolvé SOLO JSON válido (sin markdown ni explicaciones) con esta forma:
{
  "cliente": {"codigo":"10112","q":"Acme"},
  "perfil": {"codigo":"","descripcion":"aukanes presupuesto"},
  "condicionVenta": {"codigo":"180","descripcion":""},
  "fechaEntrega": "2026-07-31",
  "transporte": {"codigo":"","descripcion":"retira por deposito"},
  "expreso": "la estrella",
  "expresoDire": "san martin 2470",
  "listaPrecios": {"codigo":"","descripcion":"Ankas C"},
  "bonif1": 3,
  "bonif2": 5,
  "bonif3": 4,
  "leyenda1": "texto",
  "leyenda2": null,
  "leyenda3": null,
  "leyenda4": null,
  "leyenda5": null,
  "observaciones": "texto libre",
  "renglones":[
    {"codArticulo":"","descripcion":"AJO EN POLVO 25kg","cantidad":100,"precio":null,"porcBonif":null}
  ]
}
Reglas:
- Si un dato no aparece, omití la clave o usá null.
- Si hay artículo sin cantidad, usá cantidad 1.
- En catálogo el pack suele ir pegado a la descripción (AJO EN POLVO25 KG); igual devolvé descripcion legible y cantidad pedida aparte.
- No inventes códigos de artículo si no están legibles; preferí descripcion.
- precio y porcBonif solo si aparecen en la imagen (pueden ser null).
- cliente.codigo debe ser el código (ej. 10112), no la razón social.
- Bonificación/Bonif/Descto/Descuento 1–3 de cabecera → bonif1/bonif2/bonif3 (números; % opcional).
- “Direccion” junto a Expreso → expresoDire (texto libre). “Dirección de entrega” del cliente → omitir aquí si no es clara.
- Leyendas: hasta leyenda5.
PROMPT;

    public function __construct(
        private readonly ChatAssistantLlmGateway $llmGateway,
        private readonly CargaAsistenteArticuloTool $articuloTool,
        private readonly CargaAsistenteClienteTool $clienteTool,
        private readonly CargaAsistenteCabeceraTool $cabeceraTool,
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
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
    public function extract(
        User $user,
        string $message,
        array $normalizedImages,
        array $draftContext,
        ?int $credentialId,
    ): array {
        $prompt = trim($message);
        if ($prompt === '') {
            $prompt = 'Extraé cliente, transporte y renglones del/los adjunto(s) para armar el pedido.';
        }

        $reply = $this->llmGateway->generateReply(
            $user,
            $prompt,
            [],
            $normalizedImages,
            $credentialId,
            self::EXTRACTION_SYSTEM_PROMPT,
        );

        $parsed = $this->parsePedidoJson($reply);

        if ($parsed === null) {
            return [
                'replyText' => 'No pude interpretar la imagen. Probá describir el pedido por texto.',
                'actions' => [
                    [
                        'action' => 'noop',
                        'payload' => [],
                        'resultado' => 'ok',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        $actions = [];
        $pendingChoice = null;
        $replyParts = [];
        $workingDraft = $draftContext;

        $clienteQ = $this->resolveClienteQuery($parsed['cliente'] ?? null);
        if ($clienteQ !== '') {
            $clienteResult = $this->clienteTool->selectCliente($user, $workingDraft, $clienteQ, false);
            $this->mergeToolResult($clienteResult, $actions, $pendingChoice, $replyParts, $workingDraft);

            $clienteResuelto = trim((string) ($workingDraft['codCliente'] ?? '')) !== '';
            if (! $clienteResuelto && $pendingChoice === null) {
                // Cliente no encontrado: no cargar cabecera ni renglones de la imagen.
                if ($actions === []) {
                    $actions[] = [
                        'action' => 'noop',
                        'payload' => [],
                        'resultado' => 'ok',
                    ];
                }

                return [
                    'replyText' => implode("\n", array_values(array_unique($replyParts))) ?: 'pedidos.carga.asistente.clienteNoEncontrado',
                    'actions' => $actions,
                    'pendingChoice' => null,
                    'configurationRequired' => false,
                ];
            }
        }

        $cabeceraSteps = $this->buildCabeceraStepsFromParsed($parsed);
        $deferredCabeceraSteps = [];
        foreach ($cabeceraSteps as $index => $step) {
            if ($pendingChoice !== null) {
                $deferredCabeceraSteps = array_slice($cabeceraSteps, $index);
                break;
            }
            $stepResult = $this->executeCabeceraStep($step, $workingDraft);
            $this->mergeToolResult($stepResult, $actions, $pendingChoice, $replyParts, $workingDraft);
        }

        $codLista = max(0, (int) $workingDraft['codLista']);
        $renglonesValidos = [];
        $errores = [];
        $modificaFlags = $this->resolveModificaFlags($workingDraft);

        foreach ($parsed['renglones'] as $index => $renglon) {
            if (! is_array($renglon)) {
                continue;
            }

            $codArticulo = trim((string) ($renglon['codArticulo'] ?? $renglon['codigo'] ?? ''));
            $descripcion = trim((string) ($renglon['descripcion'] ?? ''));
            $cantidad = (float) ($renglon['cantidad'] ?? 1);
            if ($cantidad <= 0) {
                $cantidad = 1.0;
            }

            $precioRaw = $renglon['precio'] ?? null;
            $bonifRaw = $renglon['porcBonif'] ?? $renglon['bonif'] ?? null;
            $precioFromImage = is_numeric($precioRaw) ? (float) $precioRaw : null;
            $bonifFromImage = is_numeric($bonifRaw) ? (float) $bonifRaw : null;

            if (! ($modificaFlags['modificaPrecio'] ?? false)) {
                $precioFromImage = null;
            }
            if (! ($modificaFlags['modificaBonArt'] ?? false)) {
                $bonifFromImage = null;
            }

            $q = $codArticulo !== '' ? $codArticulo : $descripcion;

            if ($q === '') {
                $errores[] = ['index' => $index, 'reason' => 'sinCodigoNiDescripcion'];
                continue;
            }

            $matches = $this->articuloTool->buscarCandidatos($q, $codLista);

            if (count($matches) !== 1) {
                $errores[] = [
                    'index' => $index,
                    'q' => $q,
                    'reason' => count($matches) === 0 ? 'noEncontrado' : 'ambiguo',
                ];
                continue;
            }

            $articulo = $matches[0];
            $renglonesValidos[] = [
                'codArticulo' => $articulo['codArticulo'],
                'cantidad' => $cantidad,
                'precio' => $precioFromImage ?? $articulo['precio'],
                'porcBonif' => $bonifFromImage ?? $articulo['bonificacion'],
                'descripcion' => $articulo['descripcion'],
            ];
        }

        $hasDeferredWork = $deferredCabeceraSteps !== [] || $renglonesValidos !== [] || $errores !== [];

        if ($pendingChoice !== null && $hasDeferredWork) {
            $pendingChoice['deferredImageExtract'] = [
                'transporteQ' => '',
                'cabeceraSteps' => $deferredCabeceraSteps,
                'renglonesValidos' => $renglonesValidos,
                'errores' => $errores,
            ];
            if ($renglonesValidos !== []) {
                $replyParts[] = 'Al confirmar la elección se aplicarán '
                    .count($renglonesValidos).' renglón(es) de la imagen.';
            } elseif ($errores !== []) {
                $replyParts[] = 'Tras confirmar, se informarán los renglones sin match de la imagen.';
            } elseif ($deferredCabeceraSteps !== []) {
                $replyParts[] = 'Al confirmar la elección se aplicará la cabecera de la imagen.';
            }
        } elseif ($pendingChoice === null && ($renglonesValidos !== [] || $errores !== [])) {
            $actions[] = [
                'action' => 'applyImageExtract',
                'payload' => [
                    'renglonesValidos' => $renglonesValidos,
                    'errores' => $errores,
                ],
                'resultado' => 'ok',
            ];

            if ($renglonesValidos !== []) {
                $replyParts[] = 'Se aplicaron '.count($renglonesValidos).' renglón(es) desde la imagen.';
            } elseif ($parsed['renglones'] !== []) {
                $replyParts[] = 'No se pudo validar ningún renglón de la imagen.';
            }
        }

        if ($errores !== [] && $pendingChoice === null) {
            $errorQs = [];
            foreach ($errores as $error) {
                if (isset($error['q']) && is_string($error['q']) && $error['q'] !== '') {
                    $errorQs[] = $error['q'];
                }
            }
            if ($errorQs !== []) {
                $replyParts[] = 'Sin match para: '.implode('; ', array_slice($errorQs, 0, 5))
                    .(count($errorQs) > 5 ? '…' : '');
            }
        }

        if ($actions === []) {
            $actions[] = [
                'action' => 'noop',
                'payload' => [],
                'resultado' => 'ok',
            ];
            if ($replyParts === []) {
                $replyParts[] = 'No extraje datos aplicables de la imagen.';
            }
        }

        return [
            'replyText' => implode("\n", $replyParts),
            'actions' => $actions,
            'pendingChoice' => $pendingChoice,
            'configurationRequired' => false,
        ];
    }

    /**
     * Continúa un extracto diferido tras elegir/confirmar cliente (u otra choice).
     *
     * @param  array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }  $baseResult
     * @param  array{
     *     transporteQ?: mixed,
     *     renglonesValidos?: mixed,
     *     errores?: mixed
     * }  $deferred
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
    public function appendDeferredAfterChoice(
        array $baseResult,
        array $deferred,
        array $draftContext,
    ): array {
        $actions = $baseResult['actions'];
        $pendingChoice = $baseResult['pendingChoice'];
        $replyParts = [];
        $baseReply = trim((string) ($baseResult['replyText'] ?? ''));
        if ($baseReply !== '') {
            $replyParts[] = $baseReply;
        }

        $workingDraft = $draftContext;
        foreach ($actions as $action) {
            if (($action['action'] ?? '') === 'selectCliente') {
                $codCliente = trim((string) ($action['payload']['codCliente'] ?? ''));
                if ($codCliente !== '') {
                    $workingDraft['codCliente'] = $codCliente;
                }
            }
        }

        $cabeceraSteps = is_array($deferred['cabeceraSteps'] ?? null)
            ? array_values(array_filter(
                $deferred['cabeceraSteps'],
                static fn (mixed $step): bool => is_array($step),
            ))
            : [];
        $transporteQ = trim((string) ($deferred['transporteQ'] ?? ''));
        if ($transporteQ !== '') {
            array_unshift($cabeceraSteps, ['op' => 'setTransporte', 'q' => $transporteQ]);
        }

        $deferredCabeceraSteps = [];
        foreach ($cabeceraSteps as $index => $step) {
            if ($pendingChoice !== null) {
                $deferredCabeceraSteps = array_slice($cabeceraSteps, $index);
                break;
            }
            $stepResult = $this->executeCabeceraStep($step, $workingDraft);
            $this->mergeToolResult($stepResult, $actions, $pendingChoice, $replyParts, $workingDraft);
        }

        $renglonesValidos = is_array($deferred['renglonesValidos'] ?? null)
            ? array_values(array_filter(
                $deferred['renglonesValidos'],
                static fn (mixed $row): bool => is_array($row),
            ))
            : [];
        $errores = is_array($deferred['errores'] ?? null) ? $deferred['errores'] : [];

        if ($pendingChoice !== null) {
            $pendingChoice['deferredImageExtract'] = [
                'transporteQ' => '',
                'cabeceraSteps' => $deferredCabeceraSteps,
                'renglonesValidos' => $renglonesValidos,
                'errores' => $errores,
            ];
            if ($renglonesValidos !== []) {
                $replyParts[] = 'Al confirmar se aplicarán '
                    .count($renglonesValidos).' renglón(es) de la imagen.';
            }

            return [
                'replyText' => implode("\n", $replyParts),
                'actions' => $actions !== [] ? $actions : [[
                    'action' => 'noop',
                    'payload' => [],
                    'resultado' => 'ok',
                ]],
                'pendingChoice' => $pendingChoice,
                'configurationRequired' => false,
            ];
        }

        if ($renglonesValidos !== [] || $errores !== []) {
            $actions[] = [
                'action' => 'applyImageExtract',
                'payload' => [
                    'renglonesValidos' => $renglonesValidos,
                    'errores' => $errores,
                ],
                'resultado' => 'ok',
            ];
        }

        if ($renglonesValidos !== []) {
            $replyParts[] = 'Se aplicaron '.count($renglonesValidos).' renglón(es) desde la imagen.';
        }

        if ($errores !== []) {
            $errorQs = [];
            foreach ($errores as $error) {
                if (is_array($error) && isset($error['q']) && is_string($error['q']) && $error['q'] !== '') {
                    $errorQs[] = $error['q'];
                }
            }
            if ($errorQs !== []) {
                $replyParts[] = 'Sin match para: '.implode('; ', array_slice($errorQs, 0, 5))
                    .(count($errorQs) > 5 ? '…' : '');
            }
        }

        return [
            'replyText' => implode("\n", $replyParts),
            'actions' => $actions !== [] ? $actions : [[
                'action' => 'noop',
                'payload' => [],
                'resultado' => 'ok',
            ]],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @param  array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }  $toolResult
     * @param  list<array{action: string, payload: array<string, mixed>, resultado: string}>  $actions
     * @param  array{
     *     modo: string|null,
     *     perfilUsuario: string|null,
     *     codCliente: string|null,
     *     cabecera: array<string, mixed>,
     *     renglones: list<array<string, mixed>>,
     *     readOnly: bool,
     *     codLista: int
     * }  $workingDraft
     * @param  list<string>  $replyParts
     */
    private function mergeToolResult(
        array $toolResult,
        array &$actions,
        ?array &$pendingChoice,
        array &$replyParts,
        array &$workingDraft,
    ): void {
        foreach ($toolResult['actions'] as $action) {
            $actions[] = $action;

            if (($action['action'] ?? '') === 'selectCliente') {
                $codCliente = trim((string) ($action['payload']['codCliente'] ?? ''));
                if ($codCliente !== '') {
                    $workingDraft['codCliente'] = $codCliente;
                }
            }

            if (($action['action'] ?? '') === 'setCabeceraFields') {
                $fields = $action['payload']['fields'] ?? null;
                if (is_array($fields)) {
                    $workingDraft['cabecera'] = array_merge(
                        is_array($workingDraft['cabecera'] ?? null) ? $workingDraft['cabecera'] : [],
                        $fields,
                    );
                    if (isset($fields['listaPrecios']) && is_numeric($fields['listaPrecios'])) {
                        $workingDraft['codLista'] = (int) $fields['listaPrecios'];
                    }
                }
            }

            if (
                ($action['action'] ?? '') === 'setCabeceraField'
                || ($action['action'] ?? '') === 'setCampoLibre'
            ) {
                $field = trim((string) ($action['payload']['field'] ?? ''));
                if ($field !== '') {
                    $workingDraft['cabecera'] = array_merge(
                        is_array($workingDraft['cabecera'] ?? null) ? $workingDraft['cabecera'] : [],
                        [$field => $action['payload']['value'] ?? null],
                    );
                }
            }
        }

        $replyText = trim((string) ($toolResult['replyText'] ?? ''));
        if ($replyText !== '' && ! str_starts_with($replyText, 'pedidos.carga.asistente.')) {
            $replyParts[] = $replyText;
        } elseif ($replyText !== '') {
            $replyParts[] = $replyText;
        }

        if ($pendingChoice === null && is_array($toolResult['pendingChoice'] ?? null)) {
            $pendingChoice = $toolResult['pendingChoice'];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parsePedidoJson(string $reply): ?array
    {
        $trimmed = trim($reply);

        if (preg_match('/\{.*\}/s', $trimmed, $matches) === 1) {
            $trimmed = $matches[0];
        }

        $decoded = json_decode($trimmed, true);

        if (! is_array($decoded)) {
            return null;
        }

        $renglones = $decoded['renglones'] ?? $decoded['lines'] ?? $decoded['items'] ?? [];
        if (! is_array($renglones)) {
            $renglones = [];
        }

        $cliente = $decoded['cliente'] ?? null;
        $transporte = $decoded['transporte'] ?? $decoded['transporteExpress'] ?? null;

        $hasCliente = is_array($cliente) && $cliente !== [];
        $hasTransporte = is_array($transporte) && $transporte !== [];
        $normalizedRenglones = array_values(array_filter(
            $renglones,
            static fn (mixed $row): bool => is_array($row),
        ));
        $cabeceraSteps = $this->buildCabeceraStepsFromParsed($decoded);

        if (! $hasCliente && $cabeceraSteps === [] && $normalizedRenglones === []) {
            return null;
        }

        return array_merge($decoded, [
            'cliente' => $hasCliente ? $cliente : null,
            'transporte' => $hasTransporte ? $transporte : null,
            'renglones' => $normalizedRenglones,
        ]);
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return list<array<string, mixed>>
     */
    private function buildCabeceraStepsFromParsed(array $parsed): array
    {
        $steps = [];

        $perfilQ = $this->resolveCatalogQuery($parsed['perfil'] ?? null);
        if ($perfilQ !== '') {
            $steps[] = ['op' => 'setPerfil', 'q' => $perfilQ];
        }

        $condicionQ = $this->resolveCatalogQuery($parsed['condicionVenta'] ?? $parsed['condicion'] ?? null);
        if ($condicionQ !== '') {
            $steps[] = ['op' => 'setCondicionVenta', 'q' => $condicionQ];
        }

        $fecha = trim((string) ($parsed['fechaEntrega'] ?? ''));
        if ($fecha !== '') {
            $steps[] = ['op' => 'setFechaEntrega', 'value' => $fecha];
        }

        $transporteQ = $this->resolveTransporteQuery(
            is_array($parsed['transporte'] ?? null) ? $parsed['transporte'] : null,
        );
        if ($transporteQ !== '') {
            $steps[] = ['op' => 'setTransporte', 'q' => $transporteQ];
        }

        $expreso = trim((string) ($parsed['expreso'] ?? ''));
        if ($expreso !== '') {
            $steps[] = ['op' => 'setCampoLibre', 'field' => 'expreso', 'value' => $expreso];
        }

        $expresoDire = trim((string) ($parsed['expresoDire'] ?? $parsed['direccionExpreso'] ?? ''));
        if ($expresoDire !== '') {
            $steps[] = ['op' => 'setCampoLibre', 'field' => 'expresoDire', 'value' => $expresoDire];
        }

        $listaQ = $this->resolveCatalogQuery($parsed['listaPrecios'] ?? null);
        if ($listaQ !== '') {
            $steps[] = ['op' => 'setListaPrecios', 'q' => $listaQ];
        }

        foreach ([1, 2, 3] as $slot) {
            $key = 'bonif'.$slot;
            if (! array_key_exists($key, $parsed) || $parsed[$key] === null || $parsed[$key] === '') {
                continue;
            }
            $steps[] = ['op' => 'setCampoLibre', 'field' => $key, 'value' => $parsed[$key]];
        }

        foreach ([1, 2, 3, 4, 5] as $slot) {
            $key = 'leyenda'.$slot;
            if (! array_key_exists($key, $parsed) || $parsed[$key] === null) {
                continue;
            }
            $value = trim((string) $parsed[$key]);
            if ($value === '') {
                continue;
            }
            $steps[] = ['op' => 'setCampoLibre', 'field' => $key, 'value' => $value];
        }

        $observaciones = trim((string) ($parsed['observaciones'] ?? ''));
        if ($observaciones !== '') {
            $steps[] = ['op' => 'setCampoLibre', 'field' => 'observaciones', 'value' => $observaciones];
        }

        return $steps;
    }

    /**
     * @param  array<string, mixed>  $step
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
    private function executeCabeceraStep(array $step, array $draftContext): array
    {
        return match ((string) ($step['op'] ?? '')) {
            'setPerfil' => $this->cabeceraTool->setPerfil((string) ($step['q'] ?? ''), $draftContext),
            'setCondicionVenta' => $this->cabeceraTool->setCondicionVenta((string) ($step['q'] ?? ''), $draftContext),
            'setFechaEntrega' => $this->cabeceraTool->setFechaEntrega((string) ($step['value'] ?? ''), $draftContext),
            'setTransporte' => $this->cabeceraTool->setTransporte((string) ($step['q'] ?? ''), $draftContext),
            'setListaPrecios' => $this->cabeceraTool->setListaPrecios((string) ($step['q'] ?? ''), $draftContext),
            'setCampoLibre' => $this->cabeceraTool->setCampoLibre(
                (string) ($step['field'] ?? ''),
                $step['value'] ?? '',
                $draftContext,
            ),
            default => [
                'replyText' => '',
                'actions' => [],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ],
        };
    }

    /**
     * @param  array<string, mixed>|null  $catalog
     */
    private function resolveCatalogQuery(?array $catalog): string
    {
        if ($catalog === null) {
            return '';
        }

        foreach (['codigo', 'code', 'codLista', 'codPerfil', 'codCondvta', 'descripcion', 'q', 'nombre'] as $key) {
            $value = trim((string) ($catalog[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>|null  $cliente
     */
    private function resolveClienteQuery(?array $cliente): string
    {
        if ($cliente === null) {
            return '';
        }

        foreach (['codigo', 'codCliente', 'q', 'razonSocial', 'nombre'] as $key) {
            $value = trim((string) ($cliente[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>|null  $transporte
     */
    private function resolveTransporteQuery(?array $transporte): string
    {
        if ($transporte === null) {
            return '';
        }

        foreach (['codigo', 'codTranspor', 'descripcion', 'q', 'nombre'] as $key) {
            $value = trim((string) ($transporte[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $draftContext
     * @return array{
     *     modificaPrecio: bool,
     *     modificaBonArt: bool,
     *     modificaBonCli: bool,
     *     modificaListaPrec: bool,
     *     modificaCondVta: bool,
     *     modificaDirEntr: bool,
     *     modificaExpreso: bool
     * }
     */
    private function resolveModificaFlags(array $draftContext): array
    {
        $perfil = strtoupper((string) ($draftContext['perfilUsuario'] ?? 'V'));
        $functionalProfile = match ($perfil) {
            'C' => 'cliente',
            'S' => 'supervisor',
            default => 'vendedor',
        };

        return $this->parameterService->resolveModificaFlags($functionalProfile);
    }
}
