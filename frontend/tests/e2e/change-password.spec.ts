import { test, expect } from '@playwright/test';

const seedPassword = 'TestSeedPassword123';
const newPassword = 'Password123!';

const baseSession = {
  user: {
    id: 40,
    displayName: 'Cambio Clave MVP',
    login: 'cambioClave.mvp',
  },
  functionalProfile: 'cliente',
  codCliente: 'CAMBIOCL01',
  codVendedor: null,
  locale: 'es-AR',
  theme: 'light',
  firstLogin: false,
  security: {
    roles: ['Cliente'],
    accesoTotal: false,
  },
};

async function mockChangePasswordApi(
  page: import('@playwright/test').Page,
  options: {
    firstLogin?: boolean;
    passwordHashValid?: boolean;
  } = {},
) {
  let currentPassword = seedPassword;
  let firstLogin = options.firstLogin ?? false;
  const passwordHashValid = options.passwordHashValid ?? true;

  await page.route('**/api/v1/auth/login', async (route) => {
    const payload = route.request().postDataJSON() as { codigo?: string; password?: string };
    const isValidLogin =
      payload.codigo === 'cambioClave.mvp' &&
      (payload.password === currentPassword || (passwordHashValid && payload.password === newPassword));

    if (!isValidLogin) {
      await route.fulfill({
        status: 401,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 2001,
          respuesta: 'auth.invalidCredentials',
          resultado: {},
        }),
      });
      return;
    }

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          token: 'token-cambio-clave',
          ...baseSession,
          firstLogin,
        },
      }),
    });
  });

  await page.route('**/api/v1/auth/me', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          ...baseSession,
          firstLogin,
        },
      }),
    });
  });

  await page.route('**/api/v1/auth/password/change', async (route) => {
    const payload = route.request().postDataJSON() as {
      currentPassword?: string;
      newPassword?: string;
      newPasswordConfirmation?: string;
    };

    if (!payload.currentPassword || !payload.newPassword || !payload.newPasswordConfirmation) {
      await route.fulfill({
        status: 422,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 1002,
          respuesta: 'validation.failed',
          resultado: {},
        }),
      });
      return;
    }

    if (payload.currentPassword !== currentPassword) {
      await route.fulfill({
        status: 422,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 2003,
          respuesta: 'auth.invalidCurrentPassword',
          resultado: {},
        }),
      });
      return;
    }

    if (payload.newPassword !== payload.newPasswordConfirmation) {
      await route.fulfill({
        status: 422,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 1002,
          respuesta: 'auth.passwordConfirmationMismatch',
          resultado: {},
        }),
      });
      return;
    }

    currentPassword = payload.newPassword;
    firstLogin = false;

    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'auth.passwordChanged',
        resultado: {
          ...baseSession,
          firstLogin: false,
        },
      }),
    });
  });

  await page.route('**/api/v1/auth/logout', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'auth.logoutOk',
        resultado: {},
      }),
    });
  });

  await page.route('**/api/v1/user/menu', async (route) => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: [] }),
    });
  });
}

test('formulario vacio no envia cambio', async ({ page }) => {
  await mockChangePasswordApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await page.getByTestId('avatar-change-password').click();

  await page.getByTestId('changePasswordSubmit').click();
  await expect(page.getByTestId('changePasswordError').first()).toContainText('Complete todos los campos');
  await expect(page).toHaveURL(/\/change-password$/);
});

test('contraseña actual incorrecta muestra error', async ({ page }) => {
  await mockChangePasswordApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await page.getByTestId('avatar-change-password').click();

  await page.getByTestId('changePasswordCurrent').fill('wrong-password');
  await page.getByTestId('changePasswordNew').fill(newPassword);
  await page.getByTestId('changePasswordConfirm').fill(newPassword);
  await page.getByTestId('changePasswordSubmit').click();

  await expect(page.getByTestId('changePasswordError')).toContainText('actual no es correcta');
});

test('confirmacion distinta muestra error en cliente', async ({ page }) => {
  await mockChangePasswordApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await page.getByTestId('avatar-change-password').click();

  await page.getByTestId('changePasswordCurrent').fill(seedPassword);
  await page.getByTestId('changePasswordNew').fill(newPassword);
  await page.getByTestId('changePasswordConfirm').fill('Password456!');
  await page.getByTestId('changePasswordSubmit').click();

  await expect(page.getByTestId('changePasswordError').first()).toContainText('confirmación no coincide');
});

test('cambio exitoso desde avatar redirige al shell', async ({ page }) => {
  await mockChangePasswordApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await page.getByTestId('avatar-change-password').click();

  await page.getByTestId('changePasswordCurrent').fill(seedPassword);
  await page.getByTestId('changePasswordNew').fill(newPassword);
  await page.getByTestId('changePasswordConfirm').fill(newPassword);
  await page.getByTestId('changePasswordSubmit').click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByTestId('shellHeader')).toBeVisible();
});

test('login post-cambio acepta nueva clave y rechaza anterior', async ({ page }) => {
  await mockChangePasswordApi(page);

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await page.getByTestId('avatar-change-password').click();
  await page.getByTestId('changePasswordCurrent').fill(seedPassword);
  await page.getByTestId('changePasswordNew').fill(newPassword);
  await page.getByTestId('changePasswordConfirm').fill(newPassword);
  await page.getByTestId('changePasswordSubmit').click();
  await page.getByTestId('avatar-logout').click();
  await expect(page).toHaveURL(/\/login$/);

  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(newPassword);
  await page.getByTestId('login-submit').click();
  await expect(page).toHaveURL(/\/dashboard$/);

  await page.getByTestId('avatar-logout').click();
  await expect(page).toHaveURL(/\/login$/);

  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();
  await expect(page.getByTestId('auth-error-generic')).toBeVisible();
});

test('usuario firstLogin es redirigido al gate de cambio', async ({ page }) => {
  await mockChangePasswordApi(page, { firstLogin: true });

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();

  await expect(page).toHaveURL(/\/change-password$/);
  await expect(page.getByTestId('first-login-gate')).toBeVisible();
  await expect(page.getByTestId('shellSidebar')).toHaveCount(0);
});

test('firstLogin completa cambio y desbloquea shell', async ({ page }) => {
  await mockChangePasswordApi(page, { firstLogin: true });

  await page.goto('/login');
  await page.locator('input[name="codigo"]').fill('cambioClave.mvp');
  await page.locator('input[name="password"]').fill(seedPassword);
  await page.getByTestId('login-submit').click();

  await page.getByTestId('changePasswordCurrent').fill(seedPassword);
  await page.getByTestId('changePasswordNew').fill(newPassword);
  await page.getByTestId('changePasswordConfirm').fill(newPassword);
  await page.getByTestId('changePasswordSubmit').click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByTestId('shellSidebar')).toBeVisible();
});
