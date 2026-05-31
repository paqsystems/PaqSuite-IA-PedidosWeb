<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class OpenApiDocumentationTest extends TestCase
{
    public function testOpenApiSpecCanBeGenerated(): void
    {
        Artisan::call('l5-swagger:generate');

        $this->assertFileExists(storage_path('api-docs/api-docs.json'));
    }

    public function testDocumentationUiIsAccessible(): void
    {
        if (! is_file(storage_path('api-docs/api-docs.json'))) {
            Artisan::call('l5-swagger:generate');
        }

        $response = $this->get('/api/documentation');

        $response->assertOk();
    }

    public function testGeneratedSpecIncludesCorePaths(): void
    {
        $spec = $this->loadGeneratedSpec();

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/api/v1/health', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/login', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/password/forgot', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/password/reset', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/logout', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/me', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/clientes', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/comprobantes/{id}', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/dashboard/resumen', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/user/menu', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences/locale', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences', $spec['paths']);
        $this->assertArrayHasKey('patch', $spec['paths']['/api/v1/users/me/preferences']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences/theme', $spec['paths']);
        $this->assertArrayHasKey('patch', $spec['paths']['/api/v1/users/me/preferences/theme']);
    }

    public function testGeneratedSpecDocumentsTransversalSecurityRules(): void
    {
        $spec = $this->loadGeneratedSpec();

        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
        $this->assertArrayHasKey('sanctum', $spec['components']['securitySchemes']);
        $this->assertArrayHasKey('tenant', $spec['components']['securitySchemes']);
        $this->assertSame(
            'integer',
            $spec['components']['schemas']['SessionContextResultado']['properties']['inactivityTimeoutMinutes']['type'] ?? null
        );

        $healthOperation = $spec['paths']['/api/v1/health']['get'];
        $this->assertArrayNotHasKey('security', $healthOperation);

        $loginOperation = $spec['paths']['/api/v1/auth/login']['post'];
        $this->assertSame([['tenant' => []]], $loginOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $loginOperation['responses']);
        $this->assertArrayHasKey('401', $loginOperation['responses']);
        $this->assertArrayHasKey('403', $loginOperation['responses']);

        $forgotOperation = $spec['paths']['/api/v1/auth/password/forgot']['post'];
        $this->assertSame([['tenant' => []]], $forgotOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $forgotOperation['responses']);
        $this->assertArrayHasKey('422', $forgotOperation['responses']);

        $resetOperation = $spec['paths']['/api/v1/auth/password/reset']['post'];
        $this->assertSame([['tenant' => []]], $resetOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $resetOperation['responses']);
        $this->assertArrayHasKey('422', $resetOperation['responses']);

        $clientesOperation = $spec['paths']['/api/v1/clientes']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $clientesOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $clientesOperation['responses']);
        $this->assertArrayHasKey('401', $clientesOperation['responses']);
        $this->assertArrayHasKey('403', $clientesOperation['responses']);

        $comprobantesOperation = $spec['paths']['/api/v1/comprobantes/{id}']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $comprobantesOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $comprobantesOperation['responses']);
        $this->assertArrayHasKey('401', $comprobantesOperation['responses']);
        $this->assertArrayHasKey('403', $comprobantesOperation['responses']);
        $this->assertArrayHasKey('404', $comprobantesOperation['responses']);

        $dashboardOperation = $spec['paths']['/api/v1/dashboard/resumen']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $dashboardOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $dashboardOperation['responses']);
        $this->assertArrayHasKey('401', $dashboardOperation['responses']);
        $this->assertArrayHasKey('403', $dashboardOperation['responses']);

        $logoutOperation = $spec['paths']['/api/v1/auth/logout']['post'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $logoutOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $logoutOperation['responses']);
        $this->assertArrayHasKey('401', $logoutOperation['responses']);

        $meOperation = $spec['paths']['/api/v1/auth/me']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $meOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $meOperation['responses']);
        $this->assertArrayHasKey('401', $meOperation['responses']);
        $this->assertArrayHasKey('403', $meOperation['responses']);

        $menuOperation = $spec['paths']['/api/v1/user/menu']['get'];
        $this->assertSame([['sanctum' => []], ['tenant' => []]], $menuOperation['security'] ?? null);
        $this->assertArrayHasKey('400', $menuOperation['responses']);
        $this->assertArrayHasKey('401', $menuOperation['responses']);
        $this->assertArrayHasKey('403', $menuOperation['responses']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadGeneratedSpec(): array
    {
        if (! is_file(storage_path('api-docs/api-docs.json'))) {
            Artisan::call('l5-swagger:generate');
        }

        /** @var array<string, mixed> $spec */
        $spec = json_decode((string) file_get_contents(storage_path('api-docs/api-docs.json')), true);

        return $spec;
    }
}
