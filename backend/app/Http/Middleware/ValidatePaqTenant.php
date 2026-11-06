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
        // Sidecar DX Reporting: field-schema usa token, sin X-Paq-Cliente.
        if ($this->isSidecarDesignerSchemaRequest($request)) {
            return $next($request);
        }

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

    private function isSidecarDesignerSchemaRequest(Request $request): bool
    {
        if (! $request->is('api/v1/emissions/design/field-schema')) {
            return false;
        }

        $configured = trim((string) config('emissions.designer.sidecarToken', ''));
        if ($configured === '') {
            return false;
        }

        $header = trim((string) $request->header('X-Paq-Sidecar-Schema-Token', ''));

        return $header !== '' && hash_equals($configured, $header);
    }
}
