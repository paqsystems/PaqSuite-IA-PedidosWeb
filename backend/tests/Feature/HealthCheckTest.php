<?php

namespace Tests\Feature;

use Tests\TestCase;

final class HealthCheckTest extends TestCase
{
    public function testHealthEndpointReturnsOkEnvelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'ok')
            ->assertJsonPath('resultado.status', 'up')
            ->assertJsonStructure([
                'error',
                'respuesta',
                'resultado' => ['serviceName', 'status'],
            ]);
    }
}
