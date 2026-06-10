<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class PasswordRecoveryTest extends TestCase
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

    public function testForgotPasswordSendsMailUsingRequestedLocale(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'locale.it.mvp@paqsuite.local',
            'locale' => 'it',
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('respuesta', 'auth.passwordRecoveryEmailSent');

        Mail::assertSent(ResetPasswordMail::class, function (ResetPasswordMail $mail): bool {
            parse_str((string) parse_url($mail->resetUrl, PHP_URL_QUERY), $query);

            return $mail->locale === 'it'
                && str_contains($mail->render(), 'Reimposta password')
                && ($query['locale'] ?? null) === 'it'
                && filled($query['token'] ?? null);
        });
    }

    public function testForgotPasswordReturnsGenericSuccessForUnknownEmail(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'desconocido@paqsuite.local',
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('respuesta', 'auth.passwordRecoveryEmailSent');

        Mail::assertNothingSent();
    }

    public function testResetPasswordUpdatesHashAllowsLoginAndInvalidatesToken(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'primerIngreso.mvp@paqsuite.local',
            'locale' => 'es',
        ], $this->tenantHeaders())
            ->assertOk();

        $mail = $this->sentResetMail();
        parse_str((string) parse_url($mail->resetUrl, PHP_URL_QUERY), $query);
        $token = (string) ($query['token'] ?? '');

        $this->assertNotSame('', $token);

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $token,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('respuesta', 'auth.passwordResetOk');

        $user = User::query()->where('codigo', 'primerIngreso.mvp')->firstOrFail();
        $this->assertTrue(Hash::check($this->newPassword, $user->password_hash));
        $this->assertFalse($user->first_login);

        $this->postJson('/api/v1/auth/login', [
            'codigo' => 'primerIngreso.mvp',
            'password' => $this->newPassword,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.firstLogin', false);

        $this->postJson('/api/v1/auth/login', [
            'codigo' => 'primerIngreso.mvp',
            'password' => $this->seedPassword,
        ], $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.invalidCredentials');

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $token,
            'newPassword' => $this->newPassword,
            'newPasswordConfirmation' => $this->newPassword,
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'auth.passwordResetTokenInvalidOrExpired');
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

    private function sentResetMail(): ResetPasswordMail
    {
        /** @var array<int, ResetPasswordMail> $mailables */
        $mailables = Mail::sent(ResetPasswordMail::class)->map(
            static fn (mixed $sentMail): ResetPasswordMail => $sentMail instanceof ResetPasswordMail
                ? $sentMail
                : $sentMail['mailable']
        )->all();

        $this->assertCount(1, $mailables);

        return $mailables[0];
    }
}
