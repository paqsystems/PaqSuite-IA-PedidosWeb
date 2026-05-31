import { expect, test } from '@playwright/test';

test('forgot password envia locale y muestra respuesta generica', async ({ page }) => {
  let forgotPayload: { email?: string; locale?: string } | null = null;

  await page.route('**/api/v1/auth/password/forgot', async (route) => {
    forgotPayload = route.request().postDataJSON() as { email?: string; locale?: string };

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'auth.passwordRecoveryEmailSent',
        resultado: {},
      }),
    });
  });

  await page.goto('/forgot-password');
  await page.getByTestId('localeSelectorForgotPassword').locator('select').selectOption('es');
  await page.getByTestId('forgotPasswordEmail').fill('cliente.mvp@paqsuite.local');
  await page.getByTestId('forgotPasswordSubmit').click();

  await expect(page.getByTestId('forgotPasswordSuccess')).toContainText('Si el correo existe');
  await expect.poll(() => forgotPayload).not.toBeNull();
  await expect.poll(() => forgotPayload?.locale).toBe('es');
  await expect.poll(() => forgotPayload?.email).toBe('cliente.mvp@paqsuite.local');
});

test('reset password exitoso vuelve al login con aviso', async ({ page }) => {
  await page.route('**/api/v1/auth/password/reset', async (route) => {
    const payload = route.request().postDataJSON() as {
      token?: string;
      newPassword?: string;
      newPasswordConfirmation?: string;
    };

    await route.fulfill({
      status: payload.token === 'token-recuperacion' ? 200 : 422,
      contentType: 'application/json',
      body: JSON.stringify(
        payload.token === 'token-recuperacion'
          ? {
              error: 0,
              respuesta: 'auth.passwordResetOk',
              resultado: {},
            }
          : {
              error: 2006,
              respuesta: 'auth.passwordResetTokenInvalidOrExpired',
              resultado: {},
            },
      ),
    });
  });

  await page.goto('/reset-password?token=token-recuperacion');
  await page.getByTestId('localeSelectorResetPassword').locator('select').selectOption('es');
  await page.getByTestId('resetPasswordNew').fill('Password123!');
  await page.getByTestId('resetPasswordConfirm').fill('Password123!');
  await page.getByTestId('resetPasswordSubmit').click();

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByTestId('auth-notice-password-reset-success')).toContainText(
    'contraseña fue restablecida',
  );
});
