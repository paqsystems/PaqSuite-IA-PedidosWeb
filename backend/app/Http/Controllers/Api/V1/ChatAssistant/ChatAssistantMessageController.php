<?php

namespace App\Http\Controllers\Api\V1\ChatAssistant;

use App\Exceptions\ChatAssistantMessageException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatAssistant\SendChatAssistantMessageRequest;
use App\Http\Responses\ApiResponse;
use App\Services\ChatAssistant\ChatAssistantMessageService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;

final class ChatAssistantMessageController extends Controller
{
    public function __construct(
        private readonly ChatAssistantMessageService $messageService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/chat-assistant/messages",
     *     summary="Enviar consulta al Chat Asistente IA",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SendChatAssistantMessageRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta orientativa del asistente",
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeChatAssistantMessageReply")
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida o configuración no operativa")
     * )
     */
    public function store(SendChatAssistantMessageRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            return ApiResponse::success(
                $this->messageService->sendMessage(
                    $user,
                    $request->normalizedMessage(),
                    $request->normalizedImages(),
                    $request->normalizedCredentialId(),
                ),
            );
        } catch (ChatAssistantMessageException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->respuestaKey,
                $exception->httpStatus,
            );
        }
    }
}
