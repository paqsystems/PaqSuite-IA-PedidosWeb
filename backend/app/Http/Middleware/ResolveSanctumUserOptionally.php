<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve Bearer Sanctum sin exigir autenticación.
 *
 * La ruta GET /api/v1/emissions/design/field-schema (laravel-core) usa
 * EmissionDesignerSchemaAuthMiddleware, que acepta sidecar token O $request->user().
 * Sin auth:sanctum previo, user() queda null aunque el FE envíe Authorization →
 * envelope 3001 (auth.unauthenticated) y el diseñador muestra emission.error.3001.
 *
 * No aborta si falta token: el sidecar puede autenticarse solo con
 * X-Paq-Sidecar-Schema-Token (después de paq.tenant).
 */
final class ResolveSanctumUserOptionally
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            $user = Auth::guard('sanctum')->user();
            if ($user !== null) {
                Auth::guard('sanctum')->setUser($user);
                // MenuProcedimientoChecker / EmissionDesignerSchemaAuth usan Auth::user()
                // (guard default), no solo $request->user().
                Auth::setUser($user);
                $request->setUserResolver(static fn () => $user);
            }
        }

        return $next($request);
    }
}
