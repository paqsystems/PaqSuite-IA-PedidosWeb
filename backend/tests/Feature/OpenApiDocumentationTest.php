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
        if (! is_file(storage_path('api-docs/api-docs.json'))) {
            Artisan::call('l5-swagger:generate');
        }

        $spec = json_decode((string) file_get_contents(storage_path('api-docs/api-docs.json')), true);

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('/api/v1/health', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/auth/login', $spec['paths']);
        $this->assertArrayHasKey('/api/v1/users/me/preferences/locale', $spec['paths']);
    }
}
