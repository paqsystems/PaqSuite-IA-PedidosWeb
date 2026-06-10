<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Support\AuthErrorCodes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ValidatePaqTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerName = (string) config('paqsuite_tenant.headerName', 'X-Paq-Cliente');
        $tenant = trim((string) $request->header($headerName));
        $allowedClients = config('paqsuite_tenant.allowedClients', []);

        if ($tenant === '' || ! in_array($tenant, $allowedClients, true)) {
            return ApiResponse::error(
                AuthErrorCodes::tenantInvalid,
                'tenant.invalid',
                400
            );
        }

        return $next($request);
    }
}
