<?php

namespace App\Http\Controllers\Api\V1\PedidosWeb;

use App\Exceptions\AuthFlowException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\ArticuloCargaLookupService;
use App\Services\Visibility\VisibilityPermissionGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArticuloController extends Controller
{
    public function __construct(
        private readonly VisibilityPermissionGuard $visibilityPermissionGuard,
        private readonly ArticuloCargaLookupService $articuloCargaLookupService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(AuthErrorCodes::unauthenticated, 'auth.unauthenticated', 401);
        }

        try {
            $this->visibilityPermissionGuard->ensurePermission(
                $user,
                (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
                'repo'
            );
        } catch (AuthFlowException $exception) {
            return ApiResponse::error(
                $exception->errorCode(),
                $exception->respuestaKey(),
                $exception->httpStatus()
            );
        }

        $codigos = $this->parseCodigosQuery($request);
        $solicitudPorCodigos = $codigos !== [];

        $pageSize = $solicitudPorCodigos
            ? min(1000, max(count($codigos), 1))
            : min(10000, max(1, (int) ($request->query('page_size') ?? 500)));
        $codLista = (int) ($request->query('lista_precios') ?? 0);

        $items = $this->articuloCargaLookupService->buscar(
            q: filled($request->query('q')) ? (string) $request->query('q') : null,
            pageSize: $pageSize,
            codLista: $codLista,
            codigos: $codigos,
            soloCatalogo: $request->boolean('solo_catalogo'),
        );

        return ApiResponse::success(['items' => $items]);
    }

    /**
     * @return list<string>
     */
    private function parseCodigosQuery(Request $request): array
    {
        if (! filled($request->query('codigos'))) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $codigo): string => trim($codigo),
            explode(',', (string) $request->query('codigos'))
        ), static fn (string $codigo): bool => $codigo !== ''));
    }
}
