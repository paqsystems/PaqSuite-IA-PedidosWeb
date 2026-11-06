<?php

namespace Tests\Feature\Emission;

use Database\Seeders\PedidosWebEmissionPilotSeeder;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AuthenticatesPaqTenant;
use Tests\TestCase;

final class PedidosWebEmissionPilotApiTest extends TestCase
{
    use AuthenticatesPaqTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatesPaqTenant();

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);

        if (! Schema::hasTable('pq_emission_processes') || ! Schema::hasTable('pq_emission_jobs')) {
            $this->markTestSkipped('Tablas GEN-15 no desplegadas en la base de tests.');
        }

        $this->seed(PedidosWebEmissionPilotSeeder::class);
    }

    /**
     * @dataProvider pilotProcessProvider
     */
    public function testProcessesCatalogExposesDisplayNames(): void
    {
        $headers = $this->authHeadersFor('supervisor.mvp');

        $response = $this->getJson('/api/v1/emissions/processes', $headers)
            ->assertOk()
            ->assertJsonPath('error', 0);

        $items = $response->json('resultado.items');
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);

        foreach ($items as $item) {
            $this->assertIsArray($item);
            $this->assertNotSame('', (string) ($item['processCode'] ?? ''));
            $this->assertNotSame('', (string) ($item['name'] ?? ''));
            $this->assertNotSame($item['processCode'], $item['name'], 'El diseñador debe mostrar descripción, no el código.');
        }
    }

    public function testPilotProcessEmitsPdf(string $processCode): void
    {
        $headers = $this->authHeadersFor('supervisor.mvp');

        $meta = $this->getJson('/api/v1/emissions/processes/'.$processCode, $headers)
            ->assertOk()
            ->assertJsonPath('error', 0);

        $name = (string) $meta->json('resultado.item.name');
        $this->assertNotSame('', $name);
        $this->assertNotSame($processCode, $name);

        $create = $this->postJson('/api/v1/emissions/jobs', [
            'processCode' => $processCode,
            'channel' => 'pdf',
            'mode' => 'consolidated',
        ], $headers);

        $create->assertOk()->assertJsonPath('error', 0);
        $jobId = (string) $create->json('resultado.item.jobId');
        $this->assertNotSame('', $jobId);

        $download = $this->get('/api/v1/emissions/jobs/'.$jobId.'/download', $headers);
        $download->assertOk();
        $this->assertStringStartsWith('%PDF', $download->getContent());
    }

    public function testFieldSchemaAuthenticatedDoesNotReturn3001(): void
    {
        $headers = $this->authHeadersFor('supervisor.mvp');

        $anonymous = $this->getJson(
            '/api/v1/emissions/design/field-schema?processCode=pw_deudaclientes',
            $this->tenantHeaders(),
        );
        $this->assertContains(
            $anonymous->status(),
            [401, 200],
            'Sin Bearer debe fallar auth o devolver envelope 3001 según middleware.',
        );
        if ($anonymous->status() === 200) {
            $this->assertSame(3001, (int) $anonymous->json('error'));
        }

        $response = $this->getJson(
            '/api/v1/emissions/design/field-schema?processCode=pw_deudaclientes',
            $headers,
        );

        $this->assertNotSame(
            3001,
            (int) $response->json('error'),
            'Con Bearer válido no debe devolver AUTH_UNAUTHENTICATED (emission.error.3001): '.$response->getContent(),
        );
        $this->assertNotSame(401, $response->status(), $response->getContent());

        if ($response->status() === 200 && (int) $response->json('error') === 0) {
            $response->assertJsonPath('resultado.processCode', 'pw_deudaclientes');
            $this->assertNotSame('Dataset', (string) $response->json('resultado.datasetDisplayName'));
        }
    }

    /**
     * @return list<array{0: string}>
     */
    public static function pilotProcessProvider(): array
    {
        return [
            ['pw_pedidosingresados'],
            ['pw_deudaclientes'],
        ];
    }
}
