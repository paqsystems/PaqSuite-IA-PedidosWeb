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
     *     path="/api/v1/chat-assistant/me/configuration",
     *     summary="Configuración personal del Chat Asistente IA",
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
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            return ApiResponse::success(
                $this->configurationService->upsertConfiguration($user, $request->validated()),
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

        try {
            return ApiResponse::success(
                $this->configurationService->updateStatus(
                    $user,
                    (bool) $request->validated('isEnabled'),
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
}
