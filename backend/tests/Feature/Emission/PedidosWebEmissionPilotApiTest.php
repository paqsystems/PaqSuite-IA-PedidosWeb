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
    public function testPilotProcessEmitsPdf(string $processCode): void
    {
        $headers = $this->authHeadersFor('supervisor.mvp');

        $this->getJson('/api/v1/emissions/processes/'.$processCode, $headers)
            ->assertOk()
            ->assertJsonPath('error', 0);

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
