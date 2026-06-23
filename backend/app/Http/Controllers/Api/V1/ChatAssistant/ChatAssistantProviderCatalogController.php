<?php

namespace App\Http\Controllers\Api\V1\ChatAssistant;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ChatAssistant\ChatAssistantProviderCatalogService;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ChatAssistantProviderCatalogController extends Controller
{
    public function __construct(
        private readonly ChatAssistantProviderCatalogService $providerCatalogService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/chat-assistant/providers",
     *     summary="Catálogo de proveedores IA activos",
     *     tags={"ChatAssistant"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Catálogo de proveedores",
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeChatAssistantProviderCatalog")
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user() === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        return ApiResponse::success([
            'items' => $this->providerCatalogService->listActiveProviders(),
        ]);
    }
}
