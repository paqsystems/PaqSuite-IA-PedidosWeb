<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ChangePasswordTest extends TestCase
{
    private string $seedPassword;

    private string $newPassword = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);
    }

    public function testChangePasswordSuccessUpdatesHashAndFirstLogin(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');
        $userBefore = User::query()->where('codigo', 'cambioClave.mvp')->firstOrFail();
        $previousHash = $userBefore->password_hash;

        $response = $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('respuesta', 'auth.passwordChanged')
            ->assertJsonPath('resultado.firstLogin', false);

        $userAfter = $userBefore->fresh();
        $this->assertNotSame($previousHash, $userAfter->password_hash);
        $this->assertFalse($userAfter->first_login);
        $this->assertTrue(Hash::check($this->newPassword, $userAfter->password_hash));
    }

    public function testInvalidCurrentPasswordReturns422WithoutChangingHash(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');
        $previousHash = User::query()->where('codigo', 'cambioClave.mvp')->value('password_hash');

        $response = $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => 'wrong-password',
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonPath('respuesta', 'auth.invalidCurrentPassword');

        $this->assertSame($previousHash, User::query()->where('codigo', 'cambioClave.mvp')->value('password_hash'));
    }

    public function testNewPasswordSameAsCurrentReturns422(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');

        $response = $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->seedPassword,
            'newPasswordConfirmation' => $this->seedPassword,
        ], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonPath('respuesta', 'auth.newPasswordSameAsCurrent');
    }

    public function testConfirmationMismatchReturns422(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');

        $response = $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => 'Password456!',
        ], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonPath('respuesta', 'auth.passwordConfirmationMismatch');
    }

    public function testEmptyFieldsReturn422(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');

        $response = $this->postJson('/api/v1/auth/password/change', [], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testChangePasswordRequiresAuthentication(): void
    {
        $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    public function testLoginWithNewPasswordAfterChange(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');

        $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->authHeaders($token))->assertOk();

        $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cambioClave.mvp',
            'password' => $this->newPassword,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.user.login', 'cambioClave.mvp');

        $this->postJson('/api/v1/auth/login', [
            'codigo' => 'cambioClave.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.invalidCredentials');
    }

    public function testFirstLoginUserCanChangePasswordAndUnlockShell(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'codigo' => 'primerIngreso.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $loginResponse->assertOk()
            ->assertJsonPath('resultado.firstLogin', true);

        $token = (string) $loginResponse->json('resultado.token');

        $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->authHeaders($token))
            ->assertOk()
            ->assertJsonPath('resultado.firstLogin', false);

        $this->assertFalse(
            User::query()->where('codigo', 'primerIngreso.mvp')->value('first_login')
        );
    }

    public function testDisabledAccountReturns403(): void
    {
        $token = $this->loginTokenFor('cambioClave.mvp');

        $user = User::query()->where('codigo', 'cambioClave.mvp')->firstOrFail();
        $user->inhabilitado = true;
        $user->save();

        $this->postJson('/api/v1/auth/password/change', [
            'currentPassword' => $this->seedPassword,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->authHeaders($token))
            ->assertForbidden()
            ->assertJsonPath('respuesta', 'auth.accountDisabled');
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
