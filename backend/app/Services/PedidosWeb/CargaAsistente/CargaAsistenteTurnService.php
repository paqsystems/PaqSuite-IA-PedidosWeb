<?php

namespace App\Services\PedidosWeb\CargaAsistente;

use App\Exceptions\CargaAsistenteException;
use App\Exceptions\ChatAssistantMessageException;
use App\Models\User;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteArticuloTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteCabeceraTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteChequesTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteClienteTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteDeudaTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteGrabarTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteHistorialTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteImageExtractTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteStockTool;
use App\Support\CargaAsistenteErrorCodes;
use App\Support\ChatAssistantConfigurationReadiness;
use App\Support\ChatAssistantImageAttachmentValidator;
use App\Support\ChatAssistantMessageErrorCodes;
use Illuminate\Support\Facades\Log;

final class CargaAsistenteTurnService
{
    private const PREFERENCES_PATH = '/preferences';

    /** @var list<string> */
    private const MUTATION_INTENTS = [
        'selectCliente',
        'changeCliente',
        'confirmChangeCliente',
        'rejectChangeCliente',
        'chooseOption',
        'addRenglon',
        'mutateRenglon',
        'setCampoLibre',
        'setTransporte',
        'setCondicionVenta',
        'setPerfil',
        'setListaPrecios',
        'setFechaEntrega',
        'setDireccionEntrega',
        'grabarPedido',
        'grabarPresupuesto',
        'applyImageExtract',
    ];

    public function __construct(
        private readonly ChatAssistantConfigurationReadiness $configurationReadiness,
        private readonly ChatAssistantImageAttachmentValidator $imageAttachmentValidator,
        private readonly CargaAsistenteIntentDetector $intentDetector,
        private readonly CargaAsistenteClienteTool $clienteTool,
        private readonly CargaAsistenteArticuloTool $articuloTool,
        private readonly CargaAsistenteStockTool $stockTool,
        private readonly CargaAsistenteDeudaTool $deudaTool,
        private readonly CargaAsistenteChequesTool $chequesTool,
        private readonly CargaAsistenteHistorialTool $historialTool,
        private readonly CargaAsistenteCabeceraTool $cabeceraTool,
        private readonly CargaAsistenteGrabarTool $grabarTool,
        private readonly CargaAsistenteImageExtractTool $imageExtractTool,
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
     * @param  array<string, mixed>|null  $pendingChoice
     * @param  list<array{fileName?: mixed, mimeType?: mixed, contentBase64?: mixed}>  $images
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    public function processTurn(
        User $user,
        string $message,
        string $modality,
        array $draftContext,
        ?array $pendingChoice,
        array $images = [],
        ?int $credentialId = null,
    ): array {
        try {
            $this->configurationReadiness->assertOperational($user, $credentialId);
        } catch (ChatAssistantMessageException $exception) {
            throw $this->mapChatAssistantException($exception);
        }

        try {
            $normalizedImages = $this->imageAttachmentValidator->validateAndNormalize($images);
        } catch (ChatAssistantMessageException $exception) {
            throw $this->mapChatAssistantException($exception);
        }

        if ($normalizedImages !== []) {
            $configuration = $this->configurationReadiness->getConfiguration($user, $credentialId);

            if (! $configuration['supportsVision']) {
                throw new CargaAsistenteException(
                    CargaAsistenteErrorCodes::visionUnsupported,
                    'pedidos.carga.asistente.visionUnsupported',
                );
            }
        }

        $detected = $this->intentDetector->detect($message, $pendingChoice, $normalizedImages);
        $intent = $detected['intent'];
        $params = $detected['params'];

        if ($draftContext['readOnly'] && in_array($intent, self::MUTATION_INTENTS, true)) {
            $resultado = $this->deniedResult('pedidos.carga.asistente.denied');
            $this->audit($user, $modality, $intent, 'denied', $resultado);

            return $resultado;
        }

        try {
            $resultado = $this->executeIntent(
                $user,
                $intent,
                $params,
                $draftContext,
                $pendingChoice,
                $normalizedImages,
                $credentialId,
                $message,
            );
        } catch (ChatAssistantMessageException $exception) {
            throw $this->mapChatAssistantException($exception);
        }

        $action = (string) (($resultado['actions'][0]['action'] ?? 'noop'));
        $this->audit($user, $modality, $intent, $action, $resultado);

        return $resultado;
    }

    /**
     * @param  array<string, mixed>  $params
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
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function executeIntent(
        User $user,
        string $intent,
        array $params,
        array $draftContext,
        ?array $pendingChoice,
        array $normalizedImages,
        ?int $credentialId,
        string $message,
    ): array {
        return match ($intent) {
            'chooseOption' => $this->executeChooseOption(
                $user,
                $draftContext,
                $pendingChoice,
                (int) ($params['option'] ?? 0),
            ),
            'confirmChangeCliente' => $this->clienteTool->confirmChangeCliente(
                $user,
                $draftContext,
                $pendingChoice,
            ),
            'rejectChangeCliente' => $this->clienteTool->rejectChangeCliente(),
            'selectCliente' => $this->clienteTool->selectCliente(
                $user,
                $draftContext,
                (string) ($params['q'] ?? ''),
                false,
            ),
            'changeCliente' => $this->clienteTool->selectCliente(
                $user,
                $draftContext,
                (string) ($params['q'] ?? ''),
                true,
            ),
            'addRenglon' => $this->articuloTool->addRenglon(
                $draftContext,
                (string) ($params['q'] ?? ''),
                (float) ($params['cantidad'] ?? 1),
                isset($params['precio']) && $params['precio'] !== null
                    ? (float) $params['precio']
                    : null,
                isset($params['porcBonif']) && $params['porcBonif'] !== null
                    ? (float) $params['porcBonif']
                    : null,
            ),
            'mutateRenglon' => $this->articuloTool->mutateExistingRenglon(
                $draftContext,
                (string) ($params['operation'] ?? 'update'),
                (string) ($params['q'] ?? ''),
                (bool) ($params['ultimo'] ?? false),
                array_key_exists('cantidad', $params) && $params['cantidad'] !== null
                    ? (float) $params['cantidad']
                    : null,
                array_key_exists('precio', $params) && $params['precio'] !== null
                    ? (float) $params['precio']
                    : null,
                array_key_exists('porcBonif', $params) && $params['porcBonif'] !== null
                    ? (float) $params['porcBonif']
                    : null,
            ),
            'consultaStock' => $this->stockTool->consultaStock((string) ($params['q'] ?? '')),
            'consultaDeuda' => $this->deudaTool->consultaDeuda($user, $draftContext),
            'consultaCheques' => $this->chequesTool->consultaCheques($user, $draftContext),
            'consultaHistorial' => $this->historialTool->consultaHistorial($user, $draftContext),
            'setCampoLibre' => $this->cabeceraTool->setCampoLibre(
                (string) ($params['field'] ?? ''),
                $params['value'] ?? '',
                $draftContext,
            ),
            'setTransporte' => $this->cabeceraTool->setTransporte(
                (string) ($params['q'] ?? ''),
                $draftContext,
            ),
            'setCondicionVenta' => $this->cabeceraTool->setCondicionVenta(
                (string) ($params['q'] ?? ''),
                $draftContext,
            ),
            'setPerfil' => $this->cabeceraTool->setPerfil(
                (string) ($params['q'] ?? ''),
                $draftContext,
            ),
            'setListaPrecios' => $this->cabeceraTool->setListaPrecios(
                (string) ($params['q'] ?? ''),
                $draftContext,
            ),
            'setFechaEntrega' => $this->cabeceraTool->setFechaEntrega(
                (string) ($params['value'] ?? ''),
                $draftContext,
            ),
            'setDireccionEntrega' => $this->cabeceraTool->setDireccionEntrega(
                (string) ($params['q'] ?? ''),
                $draftContext,
            ),
            'grabarPedido' => $this->grabarTool->grabarPedido(),
            'grabarPresupuesto' => $this->grabarTool->grabarPresupuesto(),
            'applyImageExtract' => $this->imageExtractTool->extract(
                $user,
                $message,
                $normalizedImages,
                $draftContext,
                $credentialId,
            ),
            default => $this->helpResult(),
        };
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
    private function executeChooseOption(
        User $user,
        array $draftContext,
        ?array $pendingChoice,
        int $option,
    ): array {
        $kind = (string) ($pendingChoice['kind'] ?? '');

        if ($kind === 'articulo') {
            return $this->articuloTool->chooseOption($draftContext, $pendingChoice, $option);
        }

        if ($kind === 'renglonExistente') {
            return $this->articuloTool->chooseExistingRenglonOption($draftContext, $pendingChoice, $option);
        }

        if (in_array($kind, [
            'transporte',
            'condicionVenta',
            'perfil',
            'listaPrecios',
            'direccionEntrega',
        ], true)) {
            return $this->cabeceraTool->chooseCatalogOption($pendingChoice, $option);
        }

        return $this->clienteTool->chooseOption($user, $draftContext, $pendingChoice, $option);
    }

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function helpResult(): array
    {
        return [
            'replyText' => 'pedidos.carga.asistente.help',
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

    /**
     * @return array{
     *     replyText: string,
     *     actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *     pendingChoice: array<string, mixed>|null,
     *     configurationRequired: bool
     * }
     */
    private function deniedResult(string $messageKey): array
    {
        return [
            'replyText' => $messageKey,
            'actions' => [
                [
                    'action' => 'denied',
                    'payload' => ['messageKey' => $messageKey],
                    'resultado' => 'denied',
                ],
            ],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }

    private function mapChatAssistantException(ChatAssistantMessageException $exception): CargaAsistenteException
    {
        $map = [
            ChatAssistantMessageErrorCodes::configurationRequired => [
                CargaAsistenteErrorCodes::configurationRequired,
                'pedidos.carga.asistente.configurationRequired',
                [
                    'configurationRequired' => true,
                    'preferencesPath' => self::PREFERENCES_PATH,
                ],
            ],
            ChatAssistantMessageErrorCodes::providerInactive => [
                CargaAsistenteErrorCodes::configurationRequired,
                'pedidos.carga.asistente.configurationRequired',
                [
                    'configurationRequired' => true,
                    'preferencesPath' => self::PREFERENCES_PATH,
                ],
            ],
            ChatAssistantMessageErrorCodes::visionUnsupported => [
                CargaAsistenteErrorCodes::visionUnsupported,
                'pedidos.carga.asistente.visionUnsupported',
                [],
            ],
            ChatAssistantMessageErrorCodes::imagesTooMany => [
                CargaAsistenteErrorCodes::imagesTooMany,
                'chatAssistant.imagesTooMany',
                [],
            ],
            ChatAssistantMessageErrorCodes::imageInvalidFormat => [
                CargaAsistenteErrorCodes::imageInvalidFormat,
                'chatAssistant.imageInvalidFormat',
                [],
            ],
            ChatAssistantMessageErrorCodes::imageTooLarge => [
                CargaAsistenteErrorCodes::imageTooLarge,
                'chatAssistant.imageTooLarge',
                [],
            ],
            ChatAssistantMessageErrorCodes::imageInvalidPayload => [
                CargaAsistenteErrorCodes::imageInvalidPayload,
                'chatAssistant.imageInvalidPayload',
                [],
            ],
            ChatAssistantMessageErrorCodes::providerInvocationFailed => [
                CargaAsistenteErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
                [],
            ],
            ChatAssistantMessageErrorCodes::providerUnsupported => [
                CargaAsistenteErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
                [],
            ],
        ];

        $mapped = $map[$exception->errorCode] ?? [
            CargaAsistenteErrorCodes::validationError,
            $exception->respuestaKey,
            [],
        ];

        return new CargaAsistenteException(
            $mapped[0],
            $mapped[1],
            $exception->httpStatus,
            $mapped[2],
        );
    }

    /**
     * @param  array<string, mixed>  $resultado
     */
    private function audit(User $user, string $modality, string $intent, string $action, array $resultado): void
    {
        Log::info('carga.asistente', [
            'userId' => $user->id,
            'modality' => $modality,
            'intent' => $intent,
            'action' => $action,
            'resultado' => $resultado['actions'][0]['resultado'] ?? 'ok',
        ]);
    }
}
