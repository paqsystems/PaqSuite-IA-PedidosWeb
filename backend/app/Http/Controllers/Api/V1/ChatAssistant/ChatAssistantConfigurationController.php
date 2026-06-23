<?php

namespace App\Http\Controllers\Api\V1\ChatAssistant;

use App\Exceptions\ChatAssistantConfigurationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatAssistant\UpdateChatAssistantConfigurationStatusRequest;
use App\Http\Requests\ChatAssistant\UpsertChatAssistantConfigurationRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ChatAssistant\ChatAssistantConfigurationService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ChatAssistantConfigurationController extends Controller
{
    public function __construct(
        private readonly ChatAssistantConfigurationService $configurationService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/chat-assistant/me/configurations",
     *     summary="Listar configuraciones personales del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Listado de configuraciones"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            return ApiResponse::success($this->configurationService->listConfigurations($user));
        } catch (ChatAssistantConfigurationException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat-assistant/me/configuration",
     *     summary="Configuración personal activa del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Configuración actual o estado vacío",
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeChatAssistantConfiguration")
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            return ApiResponse::success($this->configurationService->getConfiguration($user));
        } catch (ChatAssistantConfigurationException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat-assistant/me/configurations",
     *     summary="Crear configuración personal del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpsertChatAssistantConfigurationRequest")
     *     ),
     *     @OA\Response(response=200, description="Configuración creada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(UpsertChatAssistantConfigurationRequest $request): JsonResponse
    {
        return $this->persistConfiguration($request, null, true);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/chat-assistant/me/configuration",
     *     summary="Guardar configuración personal del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpsertChatAssistantConfigurationRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Configuración guardada",
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeChatAssistantConfigurationSaved")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function upsert(UpsertChatAssistantConfigurationRequest $request): JsonResponse
    {
        $credentialId = $this->resolveCredentialIdFromPayload($request->validated());

        return $this->persistConfiguration($request, $credentialId, false);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/chat-assistant/me/configurations/{credentialId}",
     *     summary="Actualizar configuración personal del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="credentialId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpsertChatAssistantConfigurationRequest")
     *     ),
     *     @OA\Response(response=200, description="Configuración actualizada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpsertChatAssistantConfigurationRequest $request, int $credentialId): JsonResponse
    {
        return $this->persistConfiguration($request, $credentialId, false);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/chat-assistant/me/configurations/{credentialId}",
     *     summary="Eliminar configuración personal del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="credentialId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Configuración eliminada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="No encontrada")
     * )
     */
    public function destroy(Request $request, int $credentialId): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->configurationService->deleteConfiguration($user, $credentialId);

            return ApiResponse::success([], 'chatAssistant.configurationDeleted');
        } catch (ChatAssistantConfigurationException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/chat-assistant/me/configuration/status",
     *     summary="Habilitar o deshabilitar la configuración del Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateChatAssistantConfigurationStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estado actualizado",
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeChatAssistantConfiguration")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function updateStatus(UpdateChatAssistantConfigurationStatusRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $credentialId = $request->validated('credentialId');

        try {
            return ApiResponse::success(
                $this->configurationService->updateStatus(
                    $user,
                    (bool) $request->validated('isEnabled'),
                    $credentialId !== null ? (int) $credentialId : null,
                ),
                'chatAssistant.configurationStatusUpdated',
            );
        } catch (ChatAssistantConfigurationException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/chat-assistant/me/configurations/{credentialId}/status",
     *     summary="Habilitar o deshabilitar una configuración específica",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Parameter(name="credentialId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateChatAssistantConfigurationStatusRequest")
     *     ),
     *     @OA\Response(response=200, description="Estado actualizado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function updateItemStatus(
        UpdateChatAssistantConfigurationStatusRequest $request,
        int $credentialId,
    ): JsonResponse {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            return ApiResponse::success(
                $this->configurationService->updateStatus(
                    $user,
                    (bool) $request->validated('isEnabled'),
                    $credentialId,
                ),
                'chatAssistant.configurationStatusUpdated',
            );
        } catch (ChatAssistantConfigurationException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }

    private function persistConfiguration(
        UpsertChatAssistantConfigurationRequest $request,
        ?int $credentialId,
        bool $createNew,
    ): JsonResponse {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        $payload = $request->validated();
        unset($payload['credentialId']);

        try {
            return ApiResponse::success(
                $this->configurationService->upsertConfiguration($user, $payload, $credentialId, $createNew),
                'chatAssistant.configurationSaved',
            );
        } catch (ChatAssistantConfigurationException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveCredentialIdFromPayload(array $payload): ?int
    {
        $credentialId = $payload['credentialId'] ?? null;

        if ($credentialId === null || $credentialId === '') {
            return null;
        }

        return (int) $credentialId;
    }
}
