<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

final class UserPreferencesTest extends TestCase
{
    private string $seedPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);
    }

    public function testShowPreferencesReturnsNormalizedLocale(): void
    {
        $token = $this->loginTokenFor('locale.en.mvp');

        $this->getJson('/api/v1/users/me/preferences', $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.locale', 'en')
            ->assertJsonPath('resultado.theme', 'generic.light')
            ->assertJsonPath('resultado.openInNewTab', false);
    }

    public function testShowPreferencesNormalizesLegacyLightTheme(): void
    {
        $token = $this->loginTokenFor('theme.light.mvp');

        $this->getJson('/api/v1/users/me/preferences', $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.theme', 'generic.light');
    }

    public function testShowPreferencesDefaultsInvalidTheme(): void
    {
        $token = $this->loginTokenFor('theme.invalid.mvp');

        $this->getJson('/api/v1/users/me/preferences', $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.theme', 'generic.light');
    }

    public function testUpdateThemePersistsInDatabase(): void
    {
        $token = $this->loginTokenFor('theme.light.mvp');

        $this->patchJson('/api/v1/users/me/preferences/theme', [
            'theme' => 'generic.dark',
        ], $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('respuesta', 'preferences.themeUpdated')
            ->assertJsonPath('resultado.theme', 'generic.dark');

        $this->assertSame(
            'generic.dark',
            User::query()->where('codigo', 'theme.light.mvp')->value('theme')
        );
    }

    public function testUpdateThemeAcceptsLegacyLightAlias(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences/theme', [
            'theme' => 'light',
        ], $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.theme', 'generic.light');
    }

    public function testUpdateThemeRejectsInvalidTheme(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences/theme', [
            'theme' => 'xx',
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'preferences.invalidTheme');
    }

    public function testUpdateThemeRejectsLocaleField(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences/theme', [
            'theme' => 'generic.dark',
            'locale' => 'en',
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testShowPreferencesReturnsOpenInNewTabTrue(): void
    {
        $token = $this->loginTokenFor('openTab.true.mvp');

        $this->getJson('/api/v1/users/me/preferences', $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.openInNewTab', true);
    }

    public function testShowPreferencesDefaultsOpenInNewTabWhenNull(): void
    {
        $token = $this->loginTokenFor('openTab.null.mvp');

        $this->getJson('/api/v1/users/me/preferences', $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.openInNewTab', false);
    }

    public function testUpdateOpenInNewTabPersistsInDatabase(): void
    {
        $token = $this->loginTokenFor('openTab.false.mvp');

        $this->patchJson('/api/v1/users/me/preferences', [
            'openInNewTab' => true,
        ], $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('respuesta', 'preferences.updated')
            ->assertJsonPath('resultado.openInNewTab', true);

        $this->assertTrue(
            (bool) User::query()->where('codigo', 'openTab.false.mvp')->value('menu_abrir_nueva_pestana')
        );
    }

    public function testUpdateOpenInNewTabRejectsLocaleField(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences', [
            'openInNewTab' => true,
            'locale' => 'en',
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testUpdateOpenInNewTabRequiresBoolean(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences', [
            'openInNewTab' => 'yes',
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testUpdateLocalePersistsInDatabase(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences/locale', [
            'locale' => 'it',
        ], $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('respuesta', 'preferences.localeUpdated')
            ->assertJsonPath('resultado.locale', 'it');

        $this->assertSame(
            'it',
            User::query()->where('codigo', 'cliente.mvp')->value('locale')
        );
    }

    public function testUpdateLocaleWithInvalidCodeReturns422(): void
    {
        $token = $this->loginTokenFor('cliente.mvp');

        $this->patchJson('/api/v1/users/me/preferences/locale', [
            'locale' => 'xx',
        ], $this->authHeaders($token))
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testPreferencesRequireAuthentication(): void
    {
        $this->getJson('/api/v1/users/me/preferences', $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');

        $this->patchJson('/api/v1/users/me/preferences/locale', [
            'locale' => 'en',
        ], $this->tenantHeaders())
            ->assertUnauthorized();

        $this->patchJson('/api/v1/users/me/preferences', [
            'openInNewTab' => true,
        ], $this->tenantHeaders())
            ->assertUnauthorized();

        $this->patchJson('/api/v1/users/me/preferences/theme', [
            'theme' => 'generic.dark',
        ], $this->tenantHeaders())
            ->assertUnauthorized();
    }

    public function testLoginReturnsNormalizedLocaleForInvalidStoredValue(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'locale.invalid.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('resultado.locale', 'es');
    }

    public function testLoginReturnsNormalizedLocaleFromBcp47(): void
    {
        $user = User::query()->where('codigo', 'cliente.mvp')->firstOrFail();
        $user->locale = 'es-AR';
        $user->save();

        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cliente.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('resultado.locale', 'es');
    }

    private function loginTokenFor(string $codigo): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => $codigo,
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk();

        return (string) $response->json('resultado.token');
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

    /**
     * @return array<string, string>
     */
    private function authHeaders(string $token): array
    {
        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$token,
        ]);
    }
}
