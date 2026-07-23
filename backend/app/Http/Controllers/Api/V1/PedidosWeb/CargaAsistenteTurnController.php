<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\CargaAsistenteException;
use App\Exceptions\ChatAssistantMessageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PedidosWeb\CargaAsistenteTurnRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\CargaAsistente\CargaAsistenteTurnService;
use App\Support\AuthErrorCodes;
use App\Support\CargaAsistenteErrorCodes;
use Illuminate\Http\JsonResponse;

final class CargaAsistenteTurnController extends Controller
{
    public function __construct(
        private readonly CargaAsistenteTurnService $turnService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/pedidos/carga/asistente/turn",
     *     operationId="pedidosCargaAsistenteTurn",
     *     summary="Turno del Asistente IA en carga de pedidos/presupuestos",
     *     description="Orquesta un turno conversacional sobre el borrador de carga (draftContext). Reusa BYOK del Chat Asistente IA; no usa corpus documental. Sin LLM configurado → error con configurationRequired.",
     *     tags={"PedidosWeb"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CargaAsistenteTurnRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Turno procesado (replyText + actions + pendingChoice)",
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeCargaAsistenteTurn")
     *     ),
     *     @OA\Response(response=400, description="Tenant invalido"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permiso de carga"),
     *     @OA\Response(
     *         response=422,
     *         description="Validación o configuración LLM no operativa (resultado.configurationRequired=true)"
     *     )
     * )
     */
    public function store(CargaAsistenteTurnRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            return ApiResponse::success(
                $this->turnService->processTurn(
                    $user,
                    $request->normalizedMessage(),
                    $request->normalizedModality(),
                    $request->normalizedDraftContext(),
                    $request->normalizedPendingChoice(),
                    $request->normalizedImages(),
                    $request->normalizedCredentialId(),
                ),
            );
        } catch (CargaAsistenteException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
                $exception->resultado !== []
                    ? $exception->resultado
                    : $this->gateResultadoIfNeeded($exception),
            );
        } catch (ChatAssistantMessageException $exception) {
            $mapped = $this->mapChatAssistantException($exception);

            return ApiResponse::error(
                $mapped['errorCode'],
                $mapped['respuestaKey'],
                $exception->httpStatus,
                $mapped['resultado'],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function gateResultadoIfNeeded(CargaAsistenteException $exception): array
    {
        if ($exception->errorCode === CargaAsistenteErrorCodes::configurationRequired) {
            return [
                'configurationRequired' => true,
                'preferencesPath' => '/preferences',
            ];
        }

        return [];
    }

    /**
     * @return array{errorCode: int, respuestaKey: string, resultado: array<string, mixed>}
     */
    private function mapChatAssistantException(ChatAssistantMessageException $exception): array
    {
        if (
            $exception->respuestaKey === 'chatAssistant.configurationRequired'
            || $exception->respuestaKey === 'chatAssistant.providerInactive'
        ) {
            return [
                'errorCode' => CargaAsistenteErrorCodes::configurationRequired,
                'respuestaKey' => 'pedidos.carga.asistente.configurationRequired',
                'resultado' => [
                    'configurationRequired' => true,
                    'preferencesPath' => '/preferences',
                ],
            ];
        }

        return [
            'errorCode' => CargaAsistenteErrorCodes::validationError,
            'respuestaKey' => $exception->respuestaKey,
            'resultado' => [],
        ];
    }
}
