<?php

namespace Tests\Support;

use App\Models\User;

trait AuthenticatesPaqTenant
{
    protected string $seedPassword = '';

    protected function setUpAuthenticatesPaqTenant(): void
    {
        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');
    }

    /**
     * @return array<string, string>
     */
    protected function tenantHeaders(): array
    {
        return [
            'X-Paq-Cliente' => 'desarrollo',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function authHeadersFor(string $codigo): array
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'codigo' => $codigo,
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $loginResponse->assertOk();

        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.(string) $loginResponse->json('resultado.token'),
        ]);
    }

    protected function actingAsSeedUser(string $codigo): User
    {
        return User::query()->where('codigo', $codigo)->firstOrFail();
    }
}
