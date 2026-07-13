<?php

namespace App\Services\PedidosWeb\CargaAsistente\Tools;

use App\Models\User;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;
use App\Services\PedidosWeb\ArticuloCargaLookupService;

final class CargaAsistenteImageExtractTool
{
    public function __construct(
        private readonly ChatAssistantLlmGateway $llmGateway,
        private readonly ArticuloCargaLookupService $articuloCargaLookupService,
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
            $prompt = 'Extraé del/los adjunto(s) un JSON con renglones de pedido. '
                .'Formato: {"renglones":[{"codArticulo":"...","descripcion":"...","cantidad":1}]}. '
                .'Solo JSON, sin markdown.';
        } else {
            $prompt .= "\n\nRespondé solo JSON: {\"renglones\":[{\"codArticulo\":\"...\",\"descripcion\":\"...\",\"cantidad\":1}]}.";
        }

        $reply = $this->llmGateway->generateReply(
            $user,
            $prompt,
            [],
            $normalizedImages,
            $credentialId,
        );

        $parsed = $this->parseRenglonesJson($reply);

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

        $codLista = max(0, (int) $draftContext['codLista']);
        $renglonesValidos = [];
        $errores = [];

        foreach ($parsed as $index => $renglon) {
            $codArticulo = trim((string) ($renglon['codArticulo'] ?? $renglon['codigo'] ?? ''));
            $descripcion = trim((string) ($renglon['descripcion'] ?? ''));
            $cantidad = (float) ($renglon['cantidad'] ?? 1);
            if ($cantidad <= 0) {
                $cantidad = 1.0;
            }

            $q = $codArticulo !== '' ? $codArticulo : $descripcion;

            if ($q === '') {
                $errores[] = ['index' => $index, 'reason' => 'sinCodigoNiDescripcion'];
                continue;
            }

            $matches = $this->articuloCargaLookupService->buscar($q, 2, $codLista);

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
                'precio' => $articulo['precio'],
                'porcBonif' => $articulo['bonificacion'],
                'descripcion' => $articulo['descripcion'],
            ];
        }

        return [
            'replyText' => count($renglonesValidos) > 0
                ? 'Se aplicaron '.count($renglonesValidos).' renglón(es) desde la imagen.'
                : 'No se pudo validar ningún renglón de la imagen.',
            'actions' => [
                [
                    'action' => 'applyImageExtract',
                    'payload' => [
                        'renglonesValidos' => $renglonesValidos,
                        'errores' => $errores,
                    ],
                    'resultado' => 'ok',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function parseRenglonesJson(string $reply): ?array
    {
        $trimmed = trim($reply);

        if (preg_match('/\{.*\}/s', $trimmed, $matches) === 1) {
            $trimmed = $matches[0];
        }

        $decoded = json_decode($trimmed, true);

        if (! is_array($decoded)) {
            return null;
        }

        $renglones = $decoded['renglones'] ?? $decoded['lines'] ?? $decoded['items'] ?? null;

        if (! is_array($renglones)) {
            return null;
        }

        return array_values(array_filter($renglones, static fn (mixed $row): bool => is_array($row)));
    }
}
