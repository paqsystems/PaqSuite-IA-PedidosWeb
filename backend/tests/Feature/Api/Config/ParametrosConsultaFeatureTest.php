<?php

namespace Tests\Feature\Api\Config;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AuthenticatesPaqTenant;
use Tests\TestCase;

final class ParametrosConsultaFeatureTest extends TestCase
{
    use AuthenticatesPaqTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatesPaqTenant();
    }

    #[Test]
    public function parametrosConsultaRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/config/parametros?programa=PedidosWeb', $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    #[Test]
    public function parametrosConsultaReturnsForbiddenWithoutRepoPermission(): void
    {
        if (! $this->ensureSeedSeguridadMvp()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para test 403.');
        }

        $this->getJson('/api/v1/config/parametros?programa=PedidosWeb', $this->authHeadersFor('vendedor.acotado.mvp'))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    private function ensureSeedSeguridadMvp(): bool
    {
        try {
            return $this->artisan('paqsuite:seed-seguridad-mvp')->run() === 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
