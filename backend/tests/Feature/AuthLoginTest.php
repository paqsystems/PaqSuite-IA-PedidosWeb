<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class AuthLoginTest extends TestCase
{
    private string $seedPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);
    }

    public function testLoginSuccessForCliente(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.functionalProfile', 'cliente')
            ->assertJsonPath('resultado.codCliente', 'CLIMVP001')
            ->assertJsonPath('resultado.inactivityTimeoutMinutes', 10)
            ->assertJsonPath('resultado.security.roles.0', 'Cliente')
            ->assertJsonStructure(['resultado' => ['token']]);
    }

    public function testLoginSuccessForVendedorAcotado(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'vendedor.acotado.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('resultado.functionalProfile', 'vendedor')
            ->assertJsonPath('resultado.codVendedor', 'VENACOT01')
            ->assertJsonPath('resultado.inactivityTimeoutMinutes', 10)
            ->assertJsonPath('resultado.security.roles.0', 'VendedorAcotado');
    }

    public function testLoginUsesConfiguredInactivityTimeoutWhenAvailable(): void
    {
        config()->set('paqsuite_auth.inactivityTimeoutMinutes', 15);

        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('resultado.inactivityTimeoutMinutes', 15);
    }

    public function testLoginFallsBackToDefaultInactivityTimeoutWhenConfigIsInvalid(): void
    {
        config()->set('paqsuite_auth.inactivityTimeoutMinutes', 0);

        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('resultado.inactivityTimeoutMinutes', 10);
    }

    public function testLoginInvalidCredentialsReturns401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => 'wrong-password',
        ], $this->tenantHeaders());

        $response->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.invalidCredentials');
    }

    public function testLoginWithoutPermissionReturns403(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'usuario.sinPermiso.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noPermission');
    }

    public function testLoginWithoutCommercialProfileReturns403(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'usuario.sinVinculo.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noCommercialProfile');
    }

    public function testLoginWithAmbiguousCommercialProfileReturns403(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'usuario.perfilAmbiguo.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.noCommercialProfile');
    }

    public function testLoginSuccessLoadsMenuForCliente(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $loginResponse->assertOk();
        $token = $loginResponse->json('resultado.token');

        $this->getJson('/api/v1/user/menu', array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]))
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure(['resultado' => [['id', 'menuKey', 'text']]]);
    }

    public function testInvalidTenantReturns400(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], ['X-Paq-Cliente' => 'tenant-invalido']);

        $response->assertStatus(400)
            ->assertJsonPath('respuesta', 'tenant.invalid');
    }

    public function testMeReturnsSameContextWithoutToken(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'supervisor.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $token = $loginResponse->json('resultado.token');
        $loginContext = $loginResponse->json('resultado');
        unset($loginContext['token']);

        $meResponse = $this->getJson('/api/v1/auth/me', array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]));

        $meResponse->assertOk()
            ->assertJsonPath('resultado', $loginContext)
            ->assertJsonMissing(['resultado' => ['token']]);
    }

    public function testLogoutInvalidatesToken(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $token = $loginResponse->json('resultado.token');

        $this->postJson('/api/v1/auth/logout', [], array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]))->assertOk();

        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/auth/me', array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]))->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    public function testMeRequiresAuthentication(): void
    {
        Sanctum::actingAs(User::query()->where('codigo', 'cliente.mvp')->firstOrFail());

        $this->getJson('/api/v1/auth/me', $this->tenantHeaders())
            ->assertOk();
    }

    /**
     * @return array<string, string>
     */
    private function tenantHeaders(): array
    {
        return [
            'X-Paq-Cliente' => 'desarrollo',
        ];
    }
}
